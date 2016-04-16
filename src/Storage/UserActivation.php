<?php
namespace AuthModule\Storage;

use AuthModule\Storage\Base as BaseStorage;
use AuthModule\Entity\User as UserEntity;

class UserActivation extends BaseStorage
{
    protected $meta_data = array(
        'conn'      => 'main',
        'table'     => 'user_activation_token',
        'primary'   => 'id',
        'fetchMode' => \PDO::FETCH_ASSOC
    );

    public function create(array $data)
    {
        return $this->ds->insert($this->meta_data['table'], $data);
    }

    /**
     * Delete by user id
     *
     * @param integer user_id
     * @return mixed
     */
    public function deleteByUserId($user_id)
    {
        return $this->ds->delete($this->meta_data['table'], array('user_id' => $user_id));
    }

    /**
     * Check if the user_id is activated or not
     *
     * @param $user_id
     * @return bool
     */
    public function isActivated($user_id)
    {
        $row = $this->ds->createQueryBuilder()
            ->select('count(id) as total')
            ->from($this->meta_data['table'], 'uat')
            ->andWhere('uat.user_id = :user_id')
            ->setParameter(':user_id', $user_id)
            ->andWhere('uat.used = 1') // <-- Check if they have used/activated their token before.
            ->execute()
            ->fetch($this->meta_data['fetchMode']);

        return $row['total'] > 0;
    }

    /**
     * Check if the user has already been activated by their token.
     *
     * @param  string $token
     * @return boolean
     */
    public function isUserActivatedByToken($token)
    {
        $row = $this->ds->createQueryBuilder()
              ->select('count(id) as total')
              ->from($this->meta_data['table'], 'uat')
              ->andWhere('uat.token = :token')
              ->setParameter(':token', $token)
              ->andWhere('uat.used = 1') // <-- Check if they have used/activated their token before.
              ->execute()
              ->fetch($this->meta_data['fetchMode']);

          return $row['total'] > 0;
    }

    /**
     * Check if a record exists for this token
     *
     * @param string $token
     * @return bool
     */
    public function existsByToken($token)
    {
        $row = $this->ds->createQueryBuilder()
              ->select('count(id) as total')
              ->from($this->meta_data['table'], 'uat')
              ->andWhere('uat.token = :token')
              ->setParameter(':token', $token)
              ->execute()
              ->fetch($this->meta_data['fetchMode']);

          return $row['total'] > 0;
    }

    /**
     * Check that this token has been used before
     *
     * @param string $token
     * @return bool
     */
    public function tokenHasBeenUsed($token)
    {
        $row = $this->ds->createQueryBuilder()
              ->select('count(id) as total')
              ->from($this->meta_data['table'], 'uat')
              ->andWhere('uat.token = :token')
              ->setParameter(':token', $token)
              ->andWhere('uat.used = 1') // <-- Check if they have used/activated their token before.
              ->execute()
              ->fetch($this->meta_data['fetchMode']);

        return $row['total'] > 0;
    }

    /**
     * Get the user is from the activation token.
     *
     * @param integer $token
     * @return mixed
     * @throws \Exception
     */
    public function getUserIDFromToken($token)
    {
        $row = $this->ds->createQueryBuilder()
              ->select('uat.user_id')
              ->from($this->meta_data['table'], 'uat')
              ->andWhere('uat.token = :token')
              ->setParameter(':token', $token)
              ->execute()
              ->fetch($this->meta_data['fetchMode']);

        if ($row === false) {
            throw new \Exception('Unable to find user id by token: ' . $token);
        }

          return $row['user_id'];
    }

    /**
     * Activate the users token record
     *
     * @param string $token
     */
    public function activateUserByToken($token)
    {
        $dateTime = new \DateTime();
        $this->ds->update(
            $this->meta_data['table'],
            array(
                'used'      => 1,
                'date_used' => $dateTime->format("Y-m-d H:i:s")
            ),
            array('token'   => $token)
        );
    }
}
