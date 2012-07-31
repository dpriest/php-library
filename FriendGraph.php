<?php
class FriendGraph {
    private $ring;
    private $FOLLOWS_KEY;
    private $FOLLOWERS_KEY;
    private $BLOCKS_KEY;
    private $BLOCKED_KEY;

    public function __construct()
    {
        $ring = new Redis();
        $ring->connect('127.0.0.1');
    	$this->ring = $ring;
    	$this->FOLLOWS_KEY = 'F';
    	$this->FOLLOWERS_KEY = 'f';
    	$this->BLOCKS_KEY = 'B';
    	$this->BLOCKED_KEY = 'b';
    }

    public function follow($from_user, $to_user)
    {
    	$forward_key = sprintf('%s:%s', $this->FOLLOWS_KEY, $from_user);
    	$forward = $this->ring->sadd($forward_key, $to_user);
    	$reverse_key = sprintf('%s:%s', $this->FOLLOWERS_KEY, $to_user);
    	$reverse = $this->ring->sadd($reverse_key, $from_user);
    	return $forward && $reverse;
    }
    public function unfollow($from_user, $to_user)
    {
    	$forward_key = sprintf('%s:%s', $this->FOLLOWS_KEY, $from_user);
    	$forward = $this->ring->srem($forward_key, $to_user);
    	$reverse_key = sprintf('%s:%s', $this->FOLLOWERS_KEY, $to_user);
    	$reverse = $this->ring->srem($reverse_key, $from_user);
    	return $forward && $reverse;
    }

    public function block($from_user, $to_user)
    {
    	$forward_key = sprintf('%s:%s', $this->BLOCKS_KEY, $from_user);
    	$forward = $this->ring->sadd($forward_key, $to_user);
    	$reverse_key = sprintf('%s:%s', $this->BLOCKED_KEY, $to_user);
    	$reverse = $this->ring->sadd($reverse_key, $from_user);
    	return $forward && $reverse;
    }
    public function unblock($from_user, $to_user)
    {
    	$forward_key = sprintf('%s:%s', $this->BLOCKS_KEY, $from_user);
    	$forward = $this->ring->srem($forward_key, $to_user);
    	$reverse_key = sprintf('%s:%s', $this->BLOCKED_KEY, $to_user);
    	$reverse = $this->ring->srem($reverse_key, $from_user);
    	return $forward && $reverse;
    }

    public function get_follows($user)
    {
    	$follows = $this->ring->smembers(sprintf('%s:%s', $this->FOLLOWS_KEY, $user));
    	$blocked = $this->ring->smembers(sprintf('%s:%s', $this->BLOCKED_KEY, $user));
    	return array_diff($follows, $blocked);
    }

    public function get_followers($user)
    {
    	$follows = $this->ring->smembers(sprintf('%s:%s', $this->FOLLOWERS_KEY, $user));
    	$blocked = $this->ring->smembers(sprintf('%s:%s', $this->BLOCKS_KEY, $user));
    	return array_diff($follows, $blocked);
    }

    public function get_blocks($user)
    {
    	return $this->ring->smembers(sprintf('%s:%s', $this->BLOCKS_KEY, $user));
    }

    public function get_blocked($user)
    {
    	return $this->ring->smembers(sprintf('%s:%s', $this->BLOCKED_KEY, $user));
    }
}