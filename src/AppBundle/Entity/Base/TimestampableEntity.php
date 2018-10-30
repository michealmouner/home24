<?php

namespace AppBundle\Entity\Base;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 *
 * @Serializer\ExclusionPolicy("all")
 * Base Entity
 *
 */
trait TimestampableEntity
{

    /**
     * @var \DateTime $created
     *
     * @Serializer\Type("DateTime<'U'>")
     * @Serializer\Expose()
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @Serializer\Type("DateTime<'U'>")
     * @Serializer\Expose()
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $updatedAt;

}
