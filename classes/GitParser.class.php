<?php

namespace PHPAnt\Core;

class GitParser
{
	public $status     = NULL;
	public $hash       = NULL;
    public $fullStatus = NULL;
    public $appDir     = NULL;
    public $remotes    = NULL;

	function analyzeStatus($status) {
		$regexes = [ 'branch-ahead'     => '/Your branch is ahead of .* by ([0-9]{0,}) commit[s]{0,1}/'
		           , 'untracked-files'  => '/Untracked files:/'
		           , 'unstaged-changes' => '/Changes not staged for commit/'
		           , 'up-to-date'       => '/Your branch is up-to-date/'
		           , 'diverged'         => '/Your branch and \'.*\' have diverged/'
		           ];

		foreach($regexes as $stage => $pattern) {
			$matches = [];
			$result = preg_match_all($pattern, $status, $matches);
			if($result > 0) {
				$this->status = $stage;
				return $stage;
			}
		}
	}

	function getGitStatus() {
        chdir($this->appDir);
        $cmd = "git status";
        $this->fullStatus = shell_exec($cmd);
        return $this->fullStatus;
	}

	function getGitHash() {
        chdir($this->appDir);
		$cmd = 'git rev-parse --short HEAD';
        $this->hash = trim(shell_exec($cmd));
	}

	function parseOrigin() {
        chdir($this->appDir);
		$cmd = 'git remote -v';
		$response = trim(shell_exec($cmd));
		$buffer = explode("\n", $response);
		$remote = [];

		// $pattern = '/(git\@.*\.git) \((fetch|push)\)/';
        $pattern = '%((https://)|(git@)).*.git%';
        foreach($buffer as $line) {
			$matches = [];
			$result = preg_match($pattern, $line,$matches);
			$this->remotes = $matches[0];
		}

		return false;
	}
} 