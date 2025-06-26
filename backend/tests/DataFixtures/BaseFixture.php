<?php

namespace App\Tests\DataFixtures;

use App\Entity\Account;
use App\Entity\Child;
use App\Entity\Household;
use App\Entity\ScheduledTransaction;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\Wishlist;
use App\Entity\WishlistItem;
use App\Enum\AmountBase;
use App\Enum\RepeatFrequency;
use App\Service\AccountService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BaseFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private AccountService $accountService;

    public function __construct(UserPasswordHasherInterface $passwordHasher, AccountService $accountService)
    {
        $this->passwordHasher = $passwordHasher;
        $this->accountService = $accountService;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('test');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'test'));
        $this->addReference('user', $user);

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $admin->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $this->addReference('admin', $admin);

        $manager->persist($user);
        $manager->persist($admin);

        $household1 = $this->createHouseholdWithData($manager, $user, 3, 2, 5);
        $household1->setName("Household 1");
        $household1->addUser($user);
        $this->addReference('household1', $household1);

        $scheduledTransaction = new ScheduledTransaction();
        $scheduledTransaction->setDescription('Test Schedule 1');
        $scheduledTransaction->setAmountBase(AmountBase::FIXED);
        $scheduledTransaction->setAmount(1);
        $child = $household1->getChildren()->first();
        $scheduledTransaction->setChild($child);
        $account = $child->getAccounts()->first();
        $scheduledTransaction->addAccount($account);
        $scheduledTransaction->setRepeatFrequency(RepeatFrequency::WEEKLY);
        $scheduledTransaction->setNextExecutionDate(new \DateTimeImmutable('2024-12-31'));
        $this->addReference('scheduledTransaction', $scheduledTransaction);
        $child->addTransactionSchedule($scheduledTransaction);
        $manager->persist($scheduledTransaction);

        $wishlist = new Wishlist();
        $child->setWishlist($wishlist);
        $wishlistItem = new WishlistItem();
        $wishlistItem->setWishlist($wishlist);
        $wishlistItem->setAmount(1);
        $wishlistItem->setPriority(1);
        $wishlistItem->setDescription('Wishlist Item 1');
        $manager->persist($wishlistItem);
        $manager->persist($wishlist);



        $household2 = $this->createHouseholdWithData($manager, $user, 2, 4, 3);
        $household2->setName("Household 2");
        $this->addReference('household2', $household2);
        $otherChild = $household2->getChildren()->first();
        $this->addReference("child_other_household", $otherChild);

        $scheduledTransactionOther = new ScheduledTransaction();
        $scheduledTransactionOther->setDescription('Test Schedule Other');
        $scheduledTransactionOther->setAmountBase(AmountBase::FIXED);
        $scheduledTransactionOther->setAmount(1);
        $child = $household2->getChildren()->first();
        $scheduledTransactionOther->setChild($child);
        $scheduledTransactionOther->setRepeatFrequency(RepeatFrequency::WEEKLY);
        $scheduledTransactionOther->setNextExecutionDate(new \DateTimeImmutable('2024-12-31'));
        $this->addReference('scheduled_transaction_other_household', $scheduledTransactionOther);
        $child->addTransactionSchedule($scheduledTransactionOther);

        $account = $child->getAccounts()->first();
        $transaction = $account->getTransactions()->first();
        $this->addReference("transaction_other_household", $transaction);

        $manager->persist($scheduledTransactionOther);

        $wishlistOther = new Wishlist();
        $otherChild->setWishlist($wishlistOther);
        $wishlistItemOther = new WishlistItem();
        $wishlistItemOther->setWishlist($wishlistOther);
        $wishlistItemOther->setAmount(1);
        $wishlistItemOther->setPriority(1);
        $wishlistItemOther->setDescription('Wishlist Item Other Child 1');
        $this->addReference("wishlist_other_household", $wishlistOther);
        $this->addReference("wishlist_item_other_household", $wishlistItemOther);
        $manager->persist($wishlistItemOther);
        $manager->persist($wishlistOther);

        $manager->flush();
    }

    private function createHouseholdWithData(
        ObjectManager $manager,
        User $user,
        int $childCount,
        int $accountsPerChild,
        int $transactionsPerAccount
    ) {
        $household = new Household();

        $manager->persist($household);

        for ($i = 1; $i <= $childCount; $i++) {
            $child = new Child();
            $child->setHousehold($household);
            $household->addChild($child);
            $child->setName("Child $i");

            $manager->persist($child);

            for ($j = 1; $j <= $accountsPerChild; $j++) {
                $account = new Account();
                $account->setName("Account $j");
                $account->setChild($child);
                $child->addAccount($account);
                $account->setColor($this->getColor($j));
                $account->setIcon($this->getIcon($j));

                $manager->persist($account);

                for ($k = 1; $k <= $transactionsPerAccount; $k++) {
                    $transaction = new Transaction();
                    $amount = $k;
                    $transaction->setAmount($amount);
                    $transaction->setAccount($account);
                    $account->addTransaction($transaction);
                    $transaction->setDescription("Transaction $k");
                    $transaction->setTransactionDate(new \DateTimeImmutable("2025-01-$k"));

                    $manager->persist($transaction);
                }

                $this->accountService->updateBalance($account);
            }
        }
        return $household;
    }

    private function getColor(int $index): string
    {
        $colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff'];
        return $colors[($index - 1) % count($colors)];
    }

    private function getIcon(int $index): string
    {
        $icons = ['star', 'piggy-bank', 'wallet', 'credit-card'];
        return $icons[($index - 1) % count($icons)];
    }
}