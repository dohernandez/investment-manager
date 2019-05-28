<?php

namespace App\Form\DataTransformer;

use App\Entity\Broker;
use App\Repository\BrokerRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChoiceToBrokerTransformer implements DataTransformerInterface
{
    /**
     * @var BrokerRepository
     */
    private $brokerRepository;

    public function __construct(BrokerRepository $brokerRepository)
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

        return (string) $value;
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
            throw new TransformationFailedException(sprintf(
                'No broker found with id "%s"',
                $value
            ));
        }

        return $broker;
    }
}
