<?php

declare(strict_types=1);

namespace ACA\ACF\FieldGroup;

use AC;
use AC\Acf\FieldGroup\Location;
use ACA\WC;
use ACP;

final class QueryFactory
{

    public function create(AC\TableScreen $table_screen): ?AC\Acf\FieldGroup\Query
    {
        switch (true) {
            case $table_screen instanceof AC\TableScreen\Media:
                return new Location\Media();
            case $table_screen instanceof AC\TableScreen\Post:
                return new Location\Post((string)$table_screen->get_post_type());
            case $table_screen instanceof AC\TableScreen\User:
                return new Location\User();
            case $table_screen instanceof ACP\TableScreen\Taxonomy:
                return new Location\Taxonomy();
            case $table_screen instanceof AC\TableScreen\Comment:
                return new Location\Comment();
            case $table_screen instanceof WC\TableScreen\Order:
                return new Location\Post('shop_order');
        }

        return null;
    }

}