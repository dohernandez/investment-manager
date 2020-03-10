<?php

namespace App\Domain\Wallet;

use App\Domain\Wallet\Exception\BookEntryNotEqualsException;
use App\Infrastructure\Date\Date;
use App\Infrastructure\Money\Money;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;

class BookEntry
{
    public const TYPE_BOOK = 'book';
    public const TYPE_YEAR = 'year';
    public const TYPE_MONTH = 'month';

    /**
     * @var int
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

    public function getId(): int
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

    public function increaseTotal(?Money $money): self
    {
        $this->total = $this->total ? $this->total->increase($money) : $money;

        return $this;
    }

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
        $bookEntry = $this->entries->filter(function (BookEntry $entry) use($name) {
            if ($entry->getName() === $name) {
                return true;
            }

            return false;
        })->first();
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
        $copyBookName = $copyBookName ? $copyBookName : $book ? $book->getName() : null;
        if (!$copyBookName) {
            throw new InvalidArgumentException('Copy book name can not be empty');
        }

        $copyBook = BookEntry::createBookEntry($copyBookName);

        // book entry year
        $year = (string)Date::getYear($date);
        $copyBookYearEntry = BookEntry::createYearEntry($book, $year);
        $copyBook->getEntries()->add($copyBookYearEntry);

        // book entry month
        $month = (string)Date::getMonth($date);
        $copyBookMonthEntry = BookEntry::createMonthEntry($copyBookYearEntry, $month);
        $copyBookYearEntry->getEntries()->add($copyBookMonthEntry);

        // set current total, year and month value
        if ($book) {
            $copyBook->setTotal($book->getTotal());

            if ($entry = $book->getBookEntry($year)) {
                $copyBookYearEntry->setTotal($entry->getTotal());

                if ($entry = $entry->getBookEntry($month)) {
                    $copyBookMonthEntry->setTotal($entry->getTotal());
                }
            }
        }

        $copyBook->increaseTotal($value);
        $copyBookYearEntry->increaseTotal($value);
        $copyBookMonthEntry->increaseTotal($value);

        return $copyBook;
    }
}
