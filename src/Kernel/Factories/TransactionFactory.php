<?php

namespace Mundipagg\Core\Kernel\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Aggregates\Transaction;
use Mundipagg\Core\Kernel\Exceptions\InvalidParamException;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\TransactionId;
use Mundipagg\Core\Kernel\ValueObjects\TransactionStatus;
use Mundipagg\Core\Kernel\ValueObjects\TransactionType;

class TransactionFactory implements FactoryInterface
{
    public function createFromPostData($postData)
    {
        $transaction = new Transaction;

        $transaction->setMundipaggId(new TransactionId($postData['id']));

        $baseStatus = explode('_', $postData['status']);
        $status = $baseStatus[0];
        for ($i = 1; $i < count($baseStatus); $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        if (!method_exists(TransactionStatus::class, $status)) {
            throw new InvalidParamException(
                "$status is not a valid TransactionStatus!",
                $status
            );
        }
        $transaction->setStatus(TransactionStatus::$status());

        $baseType = explode('_', $postData['transaction_type']);
        $type = $baseType[0];
        for ($i = 1; $i < count($baseType); $i++) {
            $type .= ucfirst(($baseType[$i]));
        }

        if (!method_exists(TransactionType::class, $type)) {
            throw new InvalidParamException(
                "$type is not a valid TransactionType!",
                $type
            );
        }
        $transaction->setTransactionType(TransactionType::$type());

        $transaction->setAmount($postData['amount']);

        $paidAmountIndex = isset($postData['paid_amount']) ? 'paid_amount' : 'amount';
        $transaction->setPaidAmount($postData[$paidAmountIndex]);

        $transaction->setAcquirerName($postData['acquirer_name']);
        $transaction->setAcquirerMessage($postData['acquirer_message']);
        $transaction->setAcquirerNsu($postData['acquirer_nsu']);
        $transaction->setAcquirerTid($postData['acquirer_tid']);
        $transaction->setAcquirerAuthCode($postData['acquirer_auth_code']);

        $createdAt = \DateTime::createFromFormat('Y-m-d\TH:i:s', $postData['created_at']);
        $transaction->setCreatedAt($createdAt);

        return $transaction;
    }

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        $transaction = new Transaction();

        $transaction->setId($dbData['id']);
        $transaction->setChargeId(new ChargeId($dbData['charge_id']));
        $transaction->setMundipaggId(new TransactionId($dbData['mundipagg_id']));

        $transaction->setAmount($dbData['amount']);
        $transaction->setPaidAmount($dbData['paid_amount']);

        $transaction->setAcquirerName($dbData['acquirer_name']);
        $transaction->setAcquirerMessage($dbData['acquirer_message']);
        $transaction->setAcquirerNsu($dbData['acquirer_nsu']);
        $transaction->setAcquirerTid($dbData['acquirer_tid']);
        $transaction->setAcquirerAuthCode($dbData['acquirer_auth_code']);

        $baseStatus = explode('_', $dbData['status']);
        $status = $baseStatus[0];
        for ($i = 1; $i < count($baseStatus); $i++) {
            $status .= ucfirst(($baseStatus[$i]));
        }

        if (!method_exists(TransactionStatus::class, $status)) {
            throw new InvalidParamException(
                "$status is not a valid TransactionStatus!",
                $status
            );
        }
        $transaction->setStatus(TransactionStatus::$status());

        $baseType = explode('_', $dbData['type']);
        $type = $baseType[0];
        for ($i = 1; $i < count($baseType); $i++) {
            $type .= ucfirst(($baseType[$i]));
        }

        if (!method_exists(TransactionType::class, $type)) {
            throw new InvalidParamException(
                "$type is not a valid TransactionType!",
                $type
            );
        }
        $transaction->setTransactionType(TransactionType::$type());

        $createdAt = \DateTime::createFromFormat('Y-m-d H:i:s', $dbData['created_at']);
        $transaction->setCreatedAt($createdAt);

        return $transaction;
    }
}