<?php

namespace GitHub\Api;

use GitHub\Api\GitData\Blobs;
use GitHub\Api\GitData\Commits;
use GitHub\Api\GitData\References;
use GitHub\Api\GitData\Tags;
use GitHub\Api\GitData\Trees;

/**
 * Getting full versions of specific files and trees in your Git repositories.
 *
 * @link   http://developer.github.com/v3/git/
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class GitData extends AbstractApi
{
    /**
     * @return Blobs
     */
    public function blobs()
    {
        return new Blobs($this->client);
    }

    /**
     * @return Commits
     */
    public function commits()
    {
        return new Commits($this->client);
    }

    /**
     * @return References
     */
    public function references()
    {
        return new References($this->client);
    }

    /**
     * @return Tags
     */
    public function tags()
    {
        return new Tags($this->client);
    }

    /**
     * @return Trees
     */
    public function trees()
    {
        return new Trees($this->client);
    }
}
