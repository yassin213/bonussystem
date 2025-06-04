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

    public function userLogin($partnerCode, $password, $email) {
        try {
            // Fetch the stored email and password hash
            $stmt = $this->pdo->prepare("
                SELECT email, passwort, partner_code
                FROM partner_info
                WHERE partner_code = :partner_code AND email = :email  
            ");
            $stmt->bindParam(':partner_code', $partnerCode, PDO::PARAM_INT);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if the user exists and verify the password
            if ($user && password_verify($password, $user['passwort'])) {
                // User is authenticated, return the user data
                return $user;
            } else {
                // Invalid login credentials
                return false;
            }
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }


    public function validatePartnerCodeAndUserId($partnerCode, $userId) {
        try {
            // Query to check if the partner_code and user_id exist in the database
            $stmt = $this->pdo->prepare("
                SELECT user_id, partner_code
                FROM user_bonus
                WHERE partner_code = :partner_code AND user_id = :user_id
            ");
            $stmt->bindParam(':partner_code', $partnerCode, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
    
            // Fetch the result
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Return true if a match is found, otherwise false
            if ($result) {
                return $result; // Return the validated data
            } else {
                return false; // No match found
            }
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }
    // Check Daily Limit
    public function hasReceivedPointsToday($userId, $partnerCode, $currentDate) {
        $stmt = $this->pdo->prepare("
            SELECT point 
            FROM user_bonus 
            WHERE user_id = :user_id 
              AND partner_code = :partner_code 
              AND DATE(timestamp) = :current_date
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':partner_code' => $partnerCode,
            ':current_date' => $currentDate
        ]);
        return $stmt->fetch();
    }
 // get user points
    public function getUserPoints($userId, $partnerCode) {
        $stmt = $this->pdo->prepare("
            SELECT point 
            FROM user_bonus 
            WHERE user_id = :user_id AND partner_code = :partner_code
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':partner_code' => $partnerCode
        ]);
        return $stmt->fetch();
    }

    // update user point
    
    public function updatePoints($userId, $partnerCode, $points, $timestamp) {
        $stmt = $this->pdo->prepare("
            UPDATE user_bonus 
            SET point = :point, timestamp = :timestamp 
            WHERE user_id = :user_id AND partner_code = :partner_code
        ");
        $stmt->execute([
            ':point' => $points,
            ':timestamp' => $timestamp,
            ':user_id' => $userId,
            ':partner_code' => $partnerCode
        ]);
    }
 //  rest point to null and add mounth and year of reward
    public function resetPointsAndAddReward($userId, $partnerCode, $currentMonth) {
        $stmt = $this->pdo->prepare("
            UPDATE user_bonus 
            SET point = 0, 
                reward = IFNULL(reward, 0) + 1, 
                reward_year = IF(reward_year IS NULL OR reward_year = '', :current_month, CONCAT(reward_year, ',', :current_month)) 
            WHERE user_id = :user_id AND partner_code = :partner_code
        ");
        $stmt->execute([
            ':current_month' => $currentMonth,
            ':user_id' => $userId,
            ':partner_code' => $partnerCode
        ]);
    }
  // update point to 1 if partner and user exist
    public function AddPoint($userId, $partnerCode, $timestamp) {
        // Check if the user and partner already exist in the table
        $stmt = $this->pdo->prepare("
            SELECT point FROM user_bonus WHERE user_id = :user_id AND partner_code = :partner_code
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':partner_code' => $partnerCode
        ]);
        $existingBonus = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($existingBonus) {
            // If the user and partner exist, update the points
            $stmt = $this->pdo->prepare("
                UPDATE user_bonus 
                SET point = point + 1, timestamp = :timestamp 
                WHERE user_id = :user_id AND partner_code = :partner_code
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':partner_code' => $partnerCode,
                ':timestamp' => $timestamp
            ]);
        } else {
            // Optionally, you can handle the case when no matching record is found
            // For now, just do nothing, as you do not want to insert a new record
            // This block can be removed or used to log an error if needed.
        }
    }
    
  // user check point over url and pin
  // Validate User ID, Partner Code, and PIN
    public function validatePin($userId, $partnerCode, $pin) {
        $stmt = $this->pdo->prepare("
            SELECT point 
            FROM user_bonus 
            WHERE user_id = :user_id AND partner_code = :partner_code AND PIN = :pin
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':partner_code' => $partnerCode,
            ':pin' => $pin
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

// Get Points
    public function getPointsImagePath($points) {
        return "images/" . $points . ".jpg";
    }

        // Destructor to close the PDO connection
        public function __destruct() {
                $this->pdo = null;
            }


} // end if class

