<?php

namespace Tests\Wallabag\CoreBundle\Command;


use Tests\Strut\StrutBundle\StrutTestCase;

class InstallCommandTest extends StrutTestCase
{
    public function setUp()
    {
        parent::setUp();

        if ($this->getClient()->getContainer()->get('doctrine')->getConnection()->getDriver() instanceof \Doctrine\DBAL\Driver\PDOPgSql\Driver) {
            /*
             * LOG:  statement: CREATE DATABASE "wallabag"
             * ERROR:  source database "template1" is being accessed by other users
             * DETAIL:  There is 1 other session using the database.
             * STATEMENT:  CREATE DATABASE "wallabag"
             * FATAL:  database "wallabag" does not exist
             *
             * http://stackoverflow.com/a/14374832/569101
             */
            $this->markTestSkipped('PostgreSQL spotted: can\'t find a good way to drop current database, skipping.');
        }
    }
}
