<?php
namespace AuthModule\Storage;

use AuthModule\Storage\Base as BaseStorage;
use AuthModule\Entity\User as UserEntity;

class User extends BaseStorage
{
    protected $meta_data = array(
        'conn'      => 'main',
        'table'     => 'user',
        'primary'   => 'id',
        'fetchMode' => \PDO::FETCH_ASSOC
    );

    /**
     * Get a blank user enitity
     *
     * @return mixed
     */
    public function getBlankEntity()
    {
        return new UserEntity();
    }

    /**
     * Make an entity
     *
     * @param  $user_data
     * @return mixed
     */
    public function makeEntity($user_data)
    {
        return new UserEntity($user_data);
    }

    /**
     * Get a user entity by its ID
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getByID($id)
    {
        $row = $this->find($id);

        if ($row === false) {
            throw new \Exception('Unable to obtain user row for id: ' . $id);
        }

        return new UserEntity($row);
    }

    /**
     * Find a user record by its ID
     *
     * @param $id
     * @return mixed
     */
    public function findByID($id)
    {
        return $this->find($id);
    }

    /**
     * Find a user record by the email
     *
     * @param  string $email
     * @return mixed
     */
    public function findByEmail($email)
    {
        return $this->ds->createQueryBuilder()
            ->select('u.*, ul.title AS level_name')
            ->from($this->meta_data['table'], 'u')
            ->leftJoin('u', 'user_level', 'ul', 'u.user_level_id = ul.id')
            ->andWhere('u.email = :email')->setParameter(':email', $email)
            ->execute()
            ->fetch($this->meta_data['fetchMode']);
    }
    /**
     * Find a user record by the email
     *
     * @param  string $email
     * @return mixed
     */
    public function getAllWithLevels()
    {
        $entities = array();

        $rows = $this->createQueryBuilder()
            ->select('u.*, ul.title user_level_title')
            ->from($this->meta_data['table'], 'u')
            ->leftJoin('u', 'user_level', 'ul', 'u.user_level_id = ul.id')
            ->execute()
            ->fetchAll($this->meta_data['fetchMode']);

        return $this->rowsToEntities($rows);
    }

    /**
     * Get a user entity by the email address
     *
     * @param  string $email
     * @return UserEntity
     * @throws \Exception
     */
    public function getByEmail($email)
    {
        $row = $this->findByEmail($email);

        if ($row === false) {
            throw new \Exception('Unable to find user record by email: ' . $email);
        }

        return new UserEntity($row);
    }

    /**
     * Get a user entity by username
     *
     * @param  string $username
     * @return UserEntity
     * @throws \Exception
     */
    public function getByUsername($username)
    {
        $row = $this->createQueryBuilder()
            ->select('u.*')
            ->from($this->meta_data['table'], 'u')
            ->andWhere('u.username = :username')
            ->setParameter(':username', $username)
            ->execute()
            ->fetch($this->meta_data['fetchMode']);

        if ($row === false) {
            throw new \Exception('Unable to find user record by username: ' . $username);
        }

        return new UserEntity($row);
    }

    /**
     * Check if a user record exists by email address
     *
     * @param $email
     * @return bool
     */
    public function existsByEmail($email)
    {
        $row = $this->ds->createQueryBuilder()
            ->select('count(id) as total')
            ->from($this->meta_data['table'], 'u')
            ->andWhere('u.email = :email')
            ->setParameter(':email', $email)
            ->execute()
            ->fetch($this->meta_data['fetchMode']);

        return $row['total'] > 0;
    }

    /**
     * Check if a user record exists by username
     *
     * @param $email
     * @return bool
     */
    public function existsByUsername($username)
    {
        $row = $this->createQueryBuilder()
            ->select('count(id) as total')
            ->from($this->meta_data['table'], 'u')
            ->andWhere('u.username = :username')
            ->setParameter(':username', $username)
            ->execute()
            ->fetch($this->meta_data['fetchMode']);

        return $row['total'] > 0;
    }

    /**
     * Check if a user record exists by User ID
     *
     * @param integer $id
     * @return bool
     */
    public function existsByID($id)
    {
        $row = $this->createQueryBuilder()
            ->select('count(id) as total')
            ->from($this->meta_data['table'], 'u')
            ->andWhere('u.id = :id')
            ->setParameter(':id', $id)
            ->execute()
            ->fetch($this->meta_data['fetchMode']);

        return $row['total'] > 0;
    }

    /**
     * Delete a user by their email address
     *
     * @param  string $email
     * @return mixed
     */
    public function deleteByEmail($email)
    {
        return $this->delete(array('email' => $email));
    }

    /**
     * Delete a user by their ID
     *
     * @param  integer $id
     * @return mixed
     */
    public function deleteByID($id)
    {
        return $this->delete(array($this->getPrimaryKey() => $id));
    }

    /**
     * Create a user record
     *
     * @param  array $user
     * @return integer
     */
    public function create(array $userData)
    {
        $this->ds->insert($this->meta_data['table'], $userData);
        return $this->ds->lastInsertId();
    }

    /**
     * Convert Assoc Array to User Entity Objects
     *
     * @param  Array $rows
     * @return Array
     */
    public function rowsToEntities($rows)
    {
        $ent = array();

        foreach ($rows as $r) {
            $ent[] = new UserEntity($r);
        }

        return $ent;
    }

    /**
    * Check the authentication fields to make sure things auth properly
    *
    * @param string $email
    * @param string $encPassword
    * @return boolean
    */
    public function checkAuth($email, $encPassword)
    {
        $row = $this->ds->createQueryBuilder()
            ->select('count(id) as total')
            ->from($this->meta_data['table'], 'u')
            ->andWhere('u.email = :email')
            ->andWhere('u.password = :password')
            ->setParameter(':email', $email)
            ->setParameter(':password', $encPassword)
            ->execute()
            ->fetch($this->meta_data['fetchMode']);

        return $row['total'] > 0;
    }
}
