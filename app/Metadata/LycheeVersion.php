<?php

namespace App\Metadata;

use App\Configs;

class LycheeVersion
{
	/**
	 * @var GitHubFunctions
	 */
	private $gitHubFunctions;

	/**
	 * @var bool
	 */
	public $isRelease;

	/**
	 * true if phpunit is present in vendor/bin/
	 * We use this to dertermine if composer install or composer install --no-dev was used.
	 *
	 * @var bool
	 */
	public $phpUnit;

	/**
	 * Base constructor.
	 *
	 * @param GitHubFunctions
	 */
	public function __construct(GitHubFunctions $githubFunctions)
	{
		$this->gitHubFunctions = $githubFunctions;
		$this->isRelease = $this->fetchReleaseInfo();
		$this->phpUnit = $this->fetchComposerInfo();
	}

	/**
	 * Returns true if we are using the release channel
	 * Returns false if we are using the git channel.
	 */
	private function fetchReleaseInfo()
	{
		return !file_exists(base_path('.git'));
	}

	/**
	 * Returns true if we are using the release channel
	 * Returns false if we are using the git channel.
	 */
	private function fetchComposerInfo()
	{
		return file_exists(base_path('vendor/bin/phpunit'));
	}

	/**
	 * Return asked information.
	 *
	 * @return array
	 */
	public function get()
	{
		$versions = [];
		$versions['channel'] = $this->isRelease ? 'release' : 'git';
		$versions['composer'] = $this->phpUnit ? 'dev' : '--no-dev';
		$versions['DB'] = $this->getDBVersion();
		$versions['Lychee'] = $this->getLycheeVersion();

		return $versions;
	}

	/**
	 * Format the version : number (commit id).
	 */
	public function format(array $info)
	{
		$ret = $info['version'];
		$ret .= (isset($info['commit']) ? ' (' . $info['commit'] . ')' : '');
		$ret .= $info['additional'] ?? '';

		return $ret;
	}

	/**
	 * @param string $version in the shape of xxyyzz
	 *
	 * @return string xx.yy.zz
	 */
	public function format_version(string $version)
	{
		return implode('.', array_map('intval', str_split($version, 2)));
	}

	/**
	 * Return the info about the database.
	 *
	 * @return array
	 */
	private function getDBVersion()
	{
		return ['version' => $this->format_version(Configs::get_value('version', '040000'))];
	}

	/**
	 * Return the information with respect to Lychee.
	 *
	 * @return array
	 */
	private function getLycheeVersion()
	{
		if ($this->isRelease) {
			// @codeCoverageIgnoreStart
			return ['version' => rtrim(@file_get_contents(base_path('version.md')))];
			// @codeCoverageIgnoreEnd
		}

		$branch = $this->gitHubFunctions->branch;
		$commit = $this->gitHubFunctions->head;
		if (!$commit && !$branch) {
			return ['version' => 'No git data found.'];
		}

		return ['version' => $branch, 'commit' => $commit, 'additional' => $this->gitHubFunctions->get_behind_text()];
	}
}