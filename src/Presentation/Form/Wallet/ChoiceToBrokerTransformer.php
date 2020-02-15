<?php

namespace App\Presentation\Form\Wallet;

use App\Application\Wallet\Repository\BrokerRepositoryInterface;
use App\Domain\Broker\Broker;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChoiceToBrokerTransformer implements DataTransformerInterface
{
    /**
     * @var BrokerRepositoryInterface
     */
    private $brokerRepository;

    public function __construct(BrokerRepositoryInterface $brokerRepository)
    {
        $this->brokerRepository = $brokerRepository;
    }

    /**
     * @{@inheritdoc}
     */
    public function transform($value)
    {
        // TODO check if transform works when edit view is created
        if ($value === null) {
            return '';
        }

        if (!$value instanceof Broker) {
            throw new \LogicException('The BrokerChoiceType can only be used with Broker objects');
        }

        return $value->getTitle();
    }

    /**
     * @{@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        $broker = $this->brokerRepository->find($value);

        if (!$broker) {
            throw new TransformationFailedException(
                sprintf(
                    'No broker found with id "%s"',
                    $value
                )
            );
        }

        return $broker;
    }
}
