<?php

namespace GitHub\Api\GitData;

use GitHub\Api\AbstractApi;
use GitHub\Exception\MissingArgumentException;

/**
 * @link   http://developer.github.com/v3/git/commits/
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class Commits extends AbstractApi
{
    public function show($username, $repository, $sha)
    {
        return $this->get('repos/'.rawurlencode($username).'/'.rawurlencode($repository).'/git/commits/'.rawurlencode($sha));
    }

    public function create($username, $repository, array $params)
    {
        if (!isset($params['message'], $params['tree'], $params['parents'])) {
            throw new MissingArgumentException(array('message', 'tree', 'parents'));
        }

        return $this->post('repos/'.rawurlencode($username).'/'.rawurlencode($repository).'/git/commits', $params);
    }
}
