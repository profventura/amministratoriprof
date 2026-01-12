<?php
namespace App\Models;
class CashFlow extends Model {
  public function addIncome($date, $categoryId, $description, $amount, $relatedPaymentId) {
    $st = $this->pdo->prepare('INSERT INTO cash_flows (flow_date,category_id,description,amount,type,related_payment_id,created_at) VALUES (?,?,?,?,\'income\',?,NOW())');
    $st->execute([$date, (int)$categoryId, $description, $amount, (int)$relatedPaymentId]);
  }
}
