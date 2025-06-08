<?php

class User
{
    private $name;
    private $surname;
    private $email;
    private $password;
    private $role;

    public function __construct($name, $surname, $email, $role)
    {
        $this->setName($name);
        $this->setSurname($surname);
        $this->setEmail($email);
        // $this->setPassword($password);
        $this->setRole($role);
    }

    // Getters
    public function getName()
    {
        return $this->name;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRole()
    {
        return $this->role;
    }

    // Setters with validation
    public function setName($name)
    {
        if (!empty($name)) {
            $this->name = htmlspecialchars(strip_tags($name));
        }
    }

    public function setSurname($surname)
    {
        if (!empty($surname)) {
            $this->surname = htmlspecialchars(strip_tags($surname));
        }
    }

    public function setEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->email = $email;
        } else {
            throw new Exception("Invalid email format");
        }
    }

    public function setPassword($password)
    {
        // Hash password before storing
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    public function setRole($role)
    {
        $validRoles = ['admin', 'user', 'seller','buyer' ,'customer']; // Define valid roles
        if (in_array($role, $validRoles)) {
            $this->role = $role;
        } else {
            throw new Exception("Invalid role specified");
        }
    }
}

?>