<?php
class User
{
    private $name;
    private $surname;
    private $email;
    private $role;

    public function __construct($name, $surname, $email, $role)
    {
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->role = $role;
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

    // Setters
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }
}
?>