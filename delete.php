<?php
// New password you want to set
$newPassword = "test";

// Generate a new random salt
$salt = bin2hex(random_bytes(16));

// Hash the new password with the salt
$hashedPassword = password_hash($newPassword . $salt, PASSWORD_BCRYPT);

// Print them out
echo "Salt: $salt\n";
echo "Hashed Password: $hashedPassword\n";
?>