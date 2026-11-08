<?php
/**
 * Pembungkus transaksi database: Transaction::run(function () { ... });
 * Jika ada exception, semua perubahan dibatalkan (rollback).
 */
final class Transaction
{
    public static function run(callable $fn)
    {
        $pdo = Database::pdo();
        if ($pdo->inTransaction()) {
            return $fn();   // sudah di dalam transaksi
        }
        $pdo->beginTransaction();
        try {
            $result = $fn();
            $pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
