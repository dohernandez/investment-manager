<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Exception\BookEntryNotEqualsException;
use App\Infrastructure\Date\Date;
use App\Infrastructure\Doctrine\Data;
use App\Infrastructure\Doctrine\DBAL\DataInterface;
use App\Infrastructure\Money\Money;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Proxy;
use InvalidArgumentException;

use ReflectionClass;

use function next;
use function reset;
use function unserialize;

class BookEntry implements DataInterface
{
    use Data;

    public const TYPE_BOOK = 'book';
    public const TYPE_YEAR = 'year';
    public const TYPE_MONTH = 'month';

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var BookEntry|null
     */
    private $parent;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Money|null
     */
    private $total;

    /**
     * @var Collection
     */
    private $entries;

    /**
     * @var BookEntryMetadata|null
     */
    private $metadata;

    public function __construct(?int $id = null)
    {
        $this->id = $id;

        $this->entries = new ArrayCollection();
    }

    public static function createBookEntry(string $name): self
    {
        $self = new static();

        $self->type = self::TYPE_BOOK;
        $self->name = $name;

        return $self;
    }

    public static function createYearEntry(BookEntry $parent, string $name): self
    {
        $self = new static();

        $self->parent = $parent;
        $self->type = self::TYPE_YEAR;
        $self->name = $name;

        return $self;
    }

    public static function createMonthEntry(BookEntry $parent, string $name): self
    {
        $self = new static();

        $self->parent = $parent;
        $self->type = self::TYPE_MONTH;
        $self->name = $name;

        return $self;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?BookEntry
    {
        return $this->parent;
    }

    public function setParent(?BookEntry $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTotal(): ?Money
    {
        return $this->total;
    }

    public function setTotal(?Money $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function reCalculateTotalFromMetadataTickets(): self
    {
        $bookEntryMetadata = $this->getMetadata();
        if (!$bookEntryMetadata) {
            return $this;
        }

        $tickets = $bookEntryMetadata->getExchangeTickets();
        if ($tickets->isEmpty()) {
            return $this;
        }

        $total = $tickets->first()->getMoney();

        while ($ticket = $tickets->next()) {
            $total = $total->increase($ticket->getMoney());
        }

        $this->setTotal($total);

        return $this;
    }

    public function increaseTotal(?Money $money): self
    {
        $this->total = $this->total ? $this->total->increase($money) : $money;

        return $this;
    }

    /**
     * @return BookEntry[]|Collection
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function setEntries(Collection $entries): self
    {
        $this->entries = $entries;

        return $this;
    }

    public function getBookEntry(string $name): ?BookEntry
    {
        $bookEntry = $this->entries->filter(
            function (BookEntry $entry) use ($name) {
                if ($entry->getName() === $name) {
                    return true;
                }

                return false;
            }
        )->first();

        return $bookEntry ? $bookEntry : null;
    }

    public function merge(?BookEntry $bookEntry): self
    {
        if (!$bookEntry) {
            return $this;
        }

        if ($this->getType() !== $bookEntry->getType() || $this->getName() !== $bookEntry->getName()) {
            throw new BookEntryNotEqualsException();
        }

        $this->setTotal($bookEntry->getTotal());
        $this->setMetadata($bookEntry->getMetadata());

        /** @var BookEntry $bEntry */
        foreach ($bookEntry->getEntries() as $bEntry) {
            $merged = false;
            /** @var BookEntry $entry */
            foreach ($this->getEntries() as $entry) {
                try {
                    $entry->merge($bEntry);
                    $merged = true;
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!$merged) {
                $bEntry->setParent($this);
                $this->entries->add($bEntry);
            }
        }

        return $this;
    }

    public static function copyBookAtDateAndIncreasedValue(
        DateTime $date,
        Money $value,
        ?BookEntry $book,
        ?string $copyBookName = null
    ): BookEntry {
        $copyBookName = self::getCopyBookName($book, $copyBookName);

        $copyBook = BookEntry::createBookEntry($copyBookName);

        // book entry year
        $year = (string)Date::getYear($date);
        $copyBookYearEntry = BookEntry::createYearEntry($copyBook, $year);
        $copyBook->getEntries()->add($copyBookYearEntry);

        // book entry month
        $month = (string)Date::getMonth($date);
        $copyBookMonthEntry = BookEntry::createMonthEntry($copyBookYearEntry, $month);
        $copyBookYearEntry->getEntries()->add($copyBookMonthEntry);

        // set current total, year and month value
        if ($book) {
            $copyBook->setTotal($book->getTotal());
            $copyBook->setMetadata($book->getMetadata());

            if ($entry = $book->getBookEntry($year)) {
                $copyBookYearEntry->setTotal($entry->getTotal());
                $copyBookYearEntry->setMetadata($entry->getMetadata());

                if ($entry = $entry->getBookEntry($month)) {
                    $copyBookMonthEntry->setTotal($entry->getTotal());
                    $copyBookMonthEntry->setMetadata($entry->getMetadata());
                }
            }
        }

        $copyBook->increaseTotal($value);
        $copyBookYearEntry->increaseTotal($value);
        $copyBookMonthEntry->increaseTotal($value);

        return $copyBook;
    }

    private static function getCopyBookName(?BookEntry $book, ?string $copyBookName = null): string
    {
        if (!$copyBookName && $book) {
            $copyBookName = $book->getName();
        }
        if (!$copyBookName) {
            throw new InvalidArgumentException('Copy book name can not be empty');
        }

        return $copyBookName;
    }

    public static function copyBookFromWindow(
        BookEntry $book,
        DateTime $fromDate,
        ?DateTime $toDate = null,
        ?string $copyBookName = null
    ): BookEntry {
        if ($book->getEntries()->isEmpty()) {
            throw new InvalidArgumentException('Book to copy is empty');
        }

        $copyBookName = self::getCopyBookName($book, $copyBookName);

        $copyBook = BookEntry::createBookEntry($copyBookName);
        $copyBook->setTotal($book->getTotal());

        // book entry year
        $year = Date::getYear($fromDate);
        $month = Date::getMonth($fromDate);

        $toYear = $toDate ? Date::getYear($toDate) : null;
        $toMonth = $toDate ? Date::getMonth($toDate) : null;
        while (true) {
            $bookYearEntry = $book->getBookEntry((string)$year);
            if (!$bookYearEntry) {
                break;
            }

            $copyBookYearEntry = BookEntry::createYearEntry($copyBook, $year);
            $copyBook->getEntries()->add($copyBookYearEntry);
            $copyBookYearEntry->setTotal($bookYearEntry->getTotal());
            $copyBookYearEntry->setMetadata($bookYearEntry->getMetadata());

            for ($m = $month; $m <= 12; $m++) {
                if ($toMonth && $year === $toYear && $m > $toMonth) {
                    break;
                }

                $bookMonthEntry = $bookYearEntry->getBookEntry((string)$m);
                if (!$bookMonthEntry) {
                    continue;
                }

                $copyBookMonthEntry = BookEntry::createMonthEntry($copyBookYearEntry, $bookMonthEntry->getName());
                $copyBookYearEntry->getEntries()->add($copyBookMonthEntry);
                $copyBookMonthEntry->setTotal($bookMonthEntry->getTotal());
                $copyBookMonthEntry->setMetadata($bookMonthEntry->getMetadata());
            }

            $year++;
            if ($toYear && $year > $toYear) {
                break;
            }

            $month = 1;
        }

        return $copyBook;
    }

    public function getMetadata(): ?BookEntryMetadata
    {
        // This is added to avoid exception in backward compatibility
        // when the entity manager hydrate the object
        return $this->metadata instanceof BookEntryMetadata ? $this->metadata : null;
    }

    public function setMetadata(?BookEntryMetadata $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Overwritten to avoid infinitive loop in parents
     *
     * @return array|mixed
     */
    public function marshalData()
    {
        $data = [];

        $reflect = new ReflectionClass(get_class($this));
        // This is for those entities that were already decorate by doctrine with the Proxy wrapper.
        if ($this instanceof Proxy) {
            $reflect = $reflect->getParentClass();
        }

        foreach ($reflect->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);

            if ($property->getName() === 'parent') {
                $data[$property->getName()] = $value ?
                [
                    'class' => static::class,
                    'id' => serialize($value->getId()),
                ] :
                null;

                continue;
            }

            $data[$property->getName()] = $this->marshalValue($value);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public static function unMarshalData($data)
    {
        $self = new static();

        $reflect = new ReflectionClass(static::class);

        foreach ($reflect->getProperties() as $property) {
            $property->setAccessible(true);

            if (!isset($data[$property->getName()])) {
                continue;
            }

            $value = $data[$property->getName()];
            if ($property->getName() === 'parent') {
                $property->setValue($self, new static(unserialize($value['id'])));
                continue;
            }

            $value = static::unMarshalValue($value);
            $property->setValue($self, $value);
        }

        return $self;
    }
}
