<?php

namespace App\Presentation\Form\Wallet;

use App\Application\Wallet\Repository\AccountRepositoryInterface;
use App\Domain\Wallet\Account;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChoiceToAccountTransformer implements DataTransformerInterface
{
    /**
     * @var AccountRepositoryInterface
     */
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
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

        $account = $this->accountRepository->find($value);

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
