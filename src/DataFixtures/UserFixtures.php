<?php
namespace App\DataFixtures;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setEmail('admin@ticketing.com');
        $user->setFirstName('admin');
        $user->setLastName('ticketing');

        $salt = md5(uniqid(mt_rand(0, 99999)) . $user->getEmail());
        $user->setSalt($salt);

        $password = $this->encoder->encodePassword($user, 'Aqwxszedc1!');
        $user->setPassword($password);
        $user->setStatus(2);

        $manager->persist($user);
        $manager->flush();
    }
}