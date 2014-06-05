<?php

namespace Blogger\BlogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ulff\PhotoWorldBundle\Entity\Photo;

/**
 * Description of PhotoFixtures
 *
 * @author ulff
 */
class PhotoFixtures extends AbstractFixture implements OrderedFixtureInterface {

    public function load(ObjectManager $manager) {
        $photo1 = new Photo();
        $photo1->setTitle('Łoś 1');
        $photo1->setDescription('Lorem ipsum dolor sit amet...');
        $photo1->setPath('2014/01/moose1.jpg');
        $photo1->setCreatedby(101);
        $photo1->setCreatedate(new \DateTime("2014-01-15 19:31:01"));
        $photo1->setAlbum($manager->merge($this->getReference('album1')));
        $manager->persist($photo1);
        
        $photo2 = new Photo();
        $photo2->setTitle('Łoś 2');
        $photo2->setDescription('Lorem ipsum dolor sit amet...');
        $photo2->setPath('2014/01/moose2.jpg');
        $photo2->setCreatedby(102);
        $photo2->setCreatedate(new \DateTime("2014-01-16 12:23:17"));
        $photo2->setAlbum($manager->merge($this->getReference('album1')));
        $manager->persist($photo2);
        
        $photo3 = new Photo();
        $photo3->setTitle('Łoś 3');
        $photo3->setDescription('Lorem ipsum dolor sit amet...');
        $photo3->setPath('2014/01/moose3.jpg');
        $photo3->setCreatedby(102);
        $photo3->setCreatedate(new \DateTime("2014-01-16 12:34:37"));
        $photo3->setAlbum($manager->merge($this->getReference('album1')));
        $manager->persist($photo3);
        
        $photo4 = new Photo();
        $photo4->setTitle('Łoś 4');
        $photo4->setDescription('Lorem ipsum dolor sit amet...');
        $photo4->setPath('2014/01/moose4.jpeg');
        $photo4->setCreatedby(104);
        $photo4->setCreatedate(new \DateTime("2014-01-17 08:56:38"));
        $photo4->setAlbum($manager->merge($this->getReference('album2')));
        $manager->persist($photo4);

        $manager->flush();
    }

    public function getOrder() {
        return 2;
    }

}
