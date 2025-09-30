<?php

namespace App\Controllers;

use App\Http\RequestDTO;
use App\Repositories\BillingRepository;
use Exception;

readonly class BillingController implements ControllerInterface
{
    public function __construct(
        private BillingRepository $billingRepository
    ) {}

    public function charge(RequestDTO $requestDTO): array
    {
        try {
            $rideId = $requestDTO->bodyParams['ride_id'];
            $userId = $requestDTO->bodyParams['user_id'];
            $amount = $requestDTO->bodyParams['amount'];
            $result = $this->billingRepository->chargeRide($rideId, $userId, $amount);

            return [
                'success' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function refund(RequestDTO $requestDTO): array
    {
        try {
            $transactionId = $requestDTO->bodyParams['transaction_id'];
            $reason = $requestDTO->bodyParams['reason'] ?? 'No reason provided';

            $result = $this->billingRepository->refund($transactionId, $reason);

            return [
                'success' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function invoices(RequestDTO $requestDTO): array
    {
        try {
            $userId = $requestDTO->bodyParams['user_id'];

            $invoices = $this->billingRepository->getInvoices($userId);

            return [
                'success' => true,
                'data' => $invoices
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function invoice(RequestDTO $requestDTO): array
    {
        try {
            $invoiceId = $requestDTO->bodyParams['invoice_id'];

            $invoice = $this->billingRepository->getInvoice($invoiceId);

            return [
                'success' => true,
                'data' => $invoice
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

}