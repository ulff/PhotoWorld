<?php

namespace Blogger\BlogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ulff\PhotoWorldBundle\Entity\Album;

/**
 * Description of AlbumFixtures
 *
 * @author ulff
 */
class AlbumFixtures extends AbstractFixture implements OrderedFixtureInterface {

    public function load(ObjectManager $manager) {
        $album1 = new Album();
        $album1->setTitle('Łosie');
        $album1->setDescription('Album z łosiami');
        $album1->setCreatedby(101);
        $album1->setCreatedate(new \DateTime("2014-01-01 18:56:11"));
        $manager->persist($album1);
        
        $album2 = new Album();
        $album2->setTitle('Nowsze łosie');
        $album2->setDescription('Lorem ipsum dolor sit amet...');
        $album2->setCreatedby(102);
        $album2->setCreatedate(new \DateTime("2014-01-07 13:14:06"));
        $manager->persist($album2);
        
        $manager->flush();
        
        $this->addReference('album1', $album1);
        $this->addReference('album2', $album2);
    }

    public function getOrder() {
        return 1;
    }

}
