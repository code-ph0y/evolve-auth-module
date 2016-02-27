<?php
namespace AuthModule\Storage;

use AuthModule\Storage\Base as BaseStorage;
use AuthModule\Entity\UserForgotToken as UserForgotTokenEntity;

class UserForgot extends BaseStorage
{
    protected $meta_data = array(
        'conn'      => 'main',
        'table'     => 'user_forgot_token',
        'primary'   => 'id',
        'fetchMode' => \PDO::FETCH_ASSOC
    );

    public function create(array $data)
    {
        return $this->ds->insert($this->meta_data['table'], $data);
    }

    /**
     * Check if the user has already used their forgot token
     *
     * @param  string   $token
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

    public function getByToken($token)
    {
        $row = $this->ds->createQueryBuilder()
            ->select('uft.*')
            ->from($this->meta_data['table'], 'uft')
            ->andWhere('uft.token = :token')
            ->setParameter(':token', $token)
            ->execute()
            ->fetch($this->meta_data['fetchMode']);

        if ($row === false) {
            throw new \Exception('Unable to find user token record from token: ' . $token);
        }

        return new UserForgotTokenEntity($row);
    }

    /**
     * Activate the users token record
     *
     * @param string $token
     */
    public function useToken($token)
    {
        $dateTime = new \DateTime();
        $this->ds->update(
            $this->meta_data['table'],
            array(
                'used'      => 1,
                'date_used' => $dateTime->format("Y-m-d H:i:s")
            ),
            array('token' => $token)
        );
    }
}
