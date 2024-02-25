<?php

declare(strict_types=1);

namespace Dullahan\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::loadClassMetadata, priority: 500)]
class DoctrineListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        if ('test' !== ($_ENV['APP_ENV'] ?? false)) {
            return;
        }

        $meta = $event->getClassMetadata();
        if (isset($meta->table['schema'])) {
            $meta->table['schema'] .= '_test';
        }

        // ManyToMany tables
        foreach (array_keys($meta->associationMappings) as $i) {
            if (!isset($meta->associationMappings[$i]['joinTable']['schema'])) {
                continue;
            }

            $meta->associationMappings[$i]['joinTable']['schema'] .= '_test';
        }
    }
}
