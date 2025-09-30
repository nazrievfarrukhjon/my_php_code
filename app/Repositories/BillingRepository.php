<?php

namespace App\Repositories;


use App\DB\Contracts\DBConnection;
use Exception;
use PDO;

class BillingRepository implements RepositoryInterface
{
    public function __construct(
        private DBConnection $primaryDB,
        private DBConnection $replicaDB,
    ) {}


    /**
     * @throws Exception
     */
    public function chargeRide(int $rideId, ?int $userId, float $amount, string $paymentMethod = 'card'): array
    {
        $connection = $this->primaryDB->connection();
        $connection->beginTransaction();

        try {
            // Insert billing transaction
            $sql = "
                INSERT INTO billing_transactions (ride_id, user_id, amount, status, payment_method)
                VALUES (:ride_id, :user_id, :amount, 'paid', :payment_method)
                RETURNING id, ride_id, user_id, amount, status, payment_method
            ";

            $stmt = $connection->prepare($sql);
            $stmt->execute([
                'ride_id' => $rideId,
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ]);

            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

            // Optionally update ride table with fare
            $updateSql = "
                UPDATE rides
                SET fare_amount = :amount, updated_at = NOW()
                WHERE id = :ride_id
            ";
            $updateStmt = $connection->prepare($updateSql);
            $updateStmt->execute([
                'amount' => $amount,
                'ride_id' => $rideId
            ]);

            $connection->commit();
            return $transaction;
        } catch (Exception $e) {
            $connection->rollBack();
            throw new Exception("Billing charge failed: " . $e->getMessage());
        }
    }

    /**
     * Refund a transaction
     */
    public function refund(int $transactionId, string $reason = 'No reason'): array
    {
        $connection = $this->db->connection();
        $connection->beginTransaction();

        try {
            // Mark transaction as refunded
            $sql = "
                UPDATE billing_transactions
                SET status = 'refunded', updated_at = NOW()
                WHERE id = :transaction_id
                RETURNING id, ride_id, user_id, amount, status
            ";

            $stmt = $connection->prepare($sql);
            $stmt->execute(['transaction_id' => $transactionId]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$transaction) {
                throw new Exception("Transaction not found");
            }

            $connection->commit();
            return $transaction;
        } catch (Exception $e) {
            $connection->rollBack();
            throw new Exception("Refund failed: " . $e->getMessage());
        }
    }

    /**
     * Get all invoices for a user
     */
    public function getInvoices(int $userId): array
    {
        $sql = "
            SELECT id, ride_id, amount, status, payment_method, created_at
            FROM billing_transactions
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ";

        $stmt = $this->db->connection()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single invoice
     */
    public function getInvoice(int $invoiceId): array
    {
        $sql = "
            SELECT id, ride_id, user_id, amount, status, payment_method, created_at
            FROM billing_transactions
            WHERE id = :invoice_id
        ";

        $stmt = $this->db->connection()->prepare($sql);
        $stmt->execute(['invoice_id' => $invoiceId]);

        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$invoice) {
            throw new Exception("Invoice not found");
        }

        return $invoice;
    }
}
