<?php

namespace Tests\Unit;

use App\Domain\Customer\Customer;
use App\Domain\Transaction\Transaction;
use App\Domain\Transaction\TransactionRepositoryInterface;
use App\Domain\Transaction\Exception\TransactionNotFoundException;
use App\Application\Transaction\Command\CreateTransactionCommand;
use App\Application\Transaction\Command\CreateTransactionHandler;
use App\Application\Transaction\Command\UpdateTransactionCommand;
use App\Application\Transaction\Command\UpdateTransactionHandler;
use App\Application\Transaction\Command\DeleteTransactionCommand;
use App\Application\Transaction\Command\DeleteTransactionHandler;
use PHPUnit\Framework\TestCase;

class TransactionServiceTest extends TestCase
{
    private TransactionRepositoryInterface $transactionRepo;
    private CreateTransactionHandler $createHandler;
    private UpdateTransactionHandler $updateHandler;
    private DeleteTransactionHandler $deleteHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transactionRepo = $this->createMock(TransactionRepositoryInterface::class);

        $this->createHandler = new CreateTransactionHandler($this->transactionRepo);
        $this->updateHandler = new UpdateTransactionHandler($this->transactionRepo);
        $this->deleteHandler = new DeleteTransactionHandler($this->transactionRepo);
    }

    private function setEntityId(object $entity, int $id): void
    {
        if ($entity instanceof \Illuminate\Database\Eloquent\Model) {
            $entity->setAttribute('id', $id);
            return;
        }
        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);
    }

    public function testCreateTransaction(): void
    {
        $customer = new Customer();
        $this->setEntityId($customer, 1);
        $amount = 100.50;

        $command = new CreateTransactionCommand($customer, $amount);

        $this->transactionRepo->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Transaction::class))
            ->willReturnCallback(function (Transaction $transaction) {
                $this->setEntityId($transaction, 123);
            });

        $response = ($this->createHandler)($command);

        $this->assertEquals(1, $response->total);
        $this->assertCount(1, $response->transactions);
        $this->assertEquals(123, $response->transactions[0]->transactionId);
        $this->assertEquals(1, $response->transactions[0]->customerId);
        $this->assertEquals($amount, $response->transactions[0]->amount);
    }

    public function testChangeAmountSuccess(): void
    {
        $customer = new Customer();
        $this->setEntityId($customer, 1);
        $transactionId = 42;
        $newAmount = 75.00;

        $transaction = new Transaction();
        $this->setEntityId($transaction, $transactionId);
        $transaction->setCustomer($customer);
        $transaction->setAmount(50.0);
        $transaction->setDate(new \DateTimeImmutable());

        $command = new UpdateTransactionCommand($customer, $transactionId, $newAmount);

        $this->transactionRepo->expects($this->once())
            ->method('findByIdAndCustomer')
            ->with($transactionId, $customer)
            ->willReturn($transaction);

        $this->transactionRepo->expects($this->once())
            ->method('save')
            ->with($transaction);

        $response = ($this->updateHandler)($command);

        $this->assertEquals(75.00, $transaction->getAmount());
        $this->assertEquals(1, $response->total);
        $this->assertEquals(42, $response->transactions[0]->transactionId);
        $this->assertEquals(1, $response->transactions[0]->customerId);
    }

    public function testRemoveTransactionSuccess(): void
    {
        $customer = new Customer();
        $transactionId = 42;

        $transaction = new Transaction();
        $command = new DeleteTransactionCommand($customer, $transactionId);

        $this->transactionRepo->expects($this->once())
            ->method('findByIdAndCustomer')
            ->with($transactionId, $customer)
            ->willReturn($transaction);

        $this->transactionRepo->expects($this->once())
            ->method('remove')
            ->with($transaction);

        $response = ($this->deleteHandler)($command);

        $this->assertEquals(0, $response->total);
        $this->assertEmpty($response->transactions);
    }
}