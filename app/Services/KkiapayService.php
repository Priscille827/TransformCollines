<?php

namespace App\Services;

use Kkiapay\Kkiapay;

class KkiapayService
{
    private Kkiapay $kkiapay;

    public function __construct()
    {
        $this->kkiapay = new Kkiapay(
            config('services.kkiapay.public_key'),
            config('services.kkiapay.private_key'),
            config('services.kkiapay.secret_key'),
            config('services.kkiapay.sandbox')
        );
    }

    /**
     * Vérifie une transaction.
     */
    public function verifierTransaction(string $transactionId): ?array
    {
        try {
            $response = $this->kkiapay->verifyTransaction($transactionId);
            return (array) $response;
        } catch (\Throwable $e) {
            \Log::error('Kkiapay verifyTransaction error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Rembourse une transaction.
     */
    public function rembourser(string $transactionId): bool
    {
        try {
            $this->kkiapay->refundTransaction($transactionId);
            return true;
        } catch (\Throwable $e) {
            \Log::error('Kkiapay refund error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Programme un payout hebdomadaire pour un vendeur.
     * rate_frequency: "1w" = 1 semaine, "1m" = 1 mois, "3j" = 3 jours
     */
    public function programmerPayout(string $telephone, string $frequence = '1w'): bool
    {
        try {
            $this->kkiapay->setupPayout([
                'algorithm'         => 'rate',
                'send_notification' => true,
                'destination_type'  => 'MOBILE_MONEY',
                'rate_frequency'    => $frequence,
                'destination'       => $telephone,
            ]);
            return true;
        } catch (\Throwable $e) {
            \Log::error('Kkiapay setupPayout error: ' . $e->getMessage());
            return false;
        }
    }
}