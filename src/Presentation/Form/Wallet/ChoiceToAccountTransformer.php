<?php

namespace App\Presentation\Form\Wallet;

use App\Application\Account\Repository\ProjectionAccountRepositoryInterface;
use App\Domain\Wallet\Account;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChoiceToAccountTransformer implements DataTransformerInterface
{
    /**
     * @var ProjectionAccountRepositoryInterface
     */
    private $projectionAccountRepository;

    public function __construct(ProjectionAccountRepositoryInterface $projectionAccountRepository)
    {
        $this->projectionAccountRepository = $projectionAccountRepository;
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

        if (!$value instanceof Account) {
            throw new \LogicException('The AccountChoiceType can only be used with Account objects');
        }

        return (string)$value;
    }

    /**
     * @{@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        $account = $this->projectionAccountRepository->find($value);

        if (!$account) {
            throw new TransformationFailedException(
                sprintf(
                    'No account found with id "%s"',
                    $value
                )
            );
        }

        return $account;
    }
}
