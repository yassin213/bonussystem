<?php
            class CardOrder {
                private $partnerCode;
                private $cardNumber;
                private $message;
                private $recipientEmail;
                private $clientEmail;

                public function __construct($recipientEmail, $clientEmail) {
                    $this->recipientEmail = $recipientEmail; // Admin email or first recipient
                    $this->clientEmail = $clientEmail; // Client email or second recipient
                }

                // Setter methods
                public function setPartnerCode($partnerCode) {
                    $this->partnerCode = htmlspecialchars($partnerCode);
                }

                public function setCardNumber($cardNumber) {
                    $this->cardNumber = htmlspecialchars($cardNumber);
                }

                public function setMessage($message) {
                    $this->message = htmlspecialchars($message);
                }
                
                public function setClientEmail($clientEmail) {
                    $this->clientEmail = htmlspecialchars($clientEmail);
                }


                // Method to validate form data
                public function validate() {
                    if (empty($this->partnerCode)) {
                        throw new Exception("Partner Code is required.");
                    }
                    if (empty($this->cardNumber)) {
                        throw new Exception("Card Number is required.");
                    }
                    if (empty($this->clientEmail)) {
                        throw new Exception("Email  is required.");
                    }
                        //Validate if the provided email is valid
                    if (!filter_var($this->clientEmail, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception("Invalid email address.");
                    }
                        
                                        // Custom email validation regex to be stricter
                    $emailPattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
                    
                    if (!preg_match($emailPattern, $this->clientEmail)) {
                        throw new Exception("Invalid email address.");
                    }

                        // Sanitize the email
                    $this->clientEmail = htmlspecialchars($this->clientEmail);

                     

                }

                // Send email to both recipients
                public function sendEmail() {
                    $subject = "Bestellung - Partner Code: {$this->partnerCode}";
                    $body = "Eine neue Kartenbestellung wurde aufgegeben.\n\n"
                        . "Partner Code: {$this->partnerCode}\n"
                        . "Kartennummer: {$this->cardNumber}\n"
                         . "Email: {$this->clientEmail}\n"
                        . "Nachricht: {$this->message}\n";

                    // Email headers
                    $headers = "From: no-reply@comeback24.de\r\n";
                  

                    // Send email to both recipients
                    if (!mail($this->recipientEmail, $subject, $body, $headers)) {
                        throw new Exception("Error sending email.");
                    }
     
                }








}

