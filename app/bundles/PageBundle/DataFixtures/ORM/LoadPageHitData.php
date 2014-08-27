<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\PageBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mautic\CoreBundle\Helper\CsvHelper;
use Mautic\PageBundle\Entity\Hit;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadPageHitData
 *
 * @package Mautic\PageBundle\DataFixtures\ORM
 */
class LoadPageHitData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get('mautic.factory');
        $repo    = $factory->getModel('page.page')->getRepository();

        $hits = CsvHelper::csv_to_array(__DIR__ . '/fakepagehitdata.csv');;
        foreach ($hits as $count => $rows) {
            $hit = new Hit();
            foreach ($rows as $col => $val) {
                if ($val != "NULL") {
                    $setter = "set" . ucfirst($col);
                    if (in_array($col, array('page', 'ipAddress'))) {
                        $hit->$setter($this->getReference($col.'-' . $val));
                    } elseif (in_array($col, array('dateHit', 'dateLeft'))) {
                        $hit->$setter(new \DateTime($val));
                    } elseif ($col == 'browserLanguages') {
                        $val = unserialize(stripslashes($val));
                        $hit->$setter($val);
                    } else {
                        $hit->$setter($val);
                    }
                }
            }
            $repo->saveEntity($hit);
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 8;
    }
}