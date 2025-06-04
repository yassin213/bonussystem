<?php


class Database {
    private $pdo;

    // Constructor to establish a PDO connection
    public function __construct($servername, $dbname, $username, $password) {
        try {
            $this->pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

   // Method to fetch partner information by partner code
   public function getPartnerInfo($partnerCode) {
    try {
        $stmt = $this->pdo->prepare("
            SELECT 
                company_name, street, number, zip, state, 
                Tel, payed_until, payed_at, email, active, klient_seit 
            FROM partner_info 
            WHERE partner_code = :partner_code
        ");
        $stmt->bindParam(':partner_code', $partnerCode, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Return the row as an associative array
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}


    public function getUserIdRangeByPartnerCode($partnerCode) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT MIN(user_id) AS first_user_id, MAX(user_id) AS last_user_id 
                FROM user_bonus 
                WHERE partner_code = :partner_code
            ");
            $stmt->bindParam(':partner_code', $partnerCode, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); // Return as an associative array
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }




} // end of class

