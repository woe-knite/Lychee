<?php

namespace App\Http\Controllers;

use App\ControllerFunctions\Update\Apply as ApplyUpdate;
use App\ControllerFunctions\Update\Check as CheckUpdate;
use App\Response;
use Exception;

/**
 * Class UpdateController.
 */
class UpdateController extends Controller
{
	/**
	 * @var ApplyUpdate
	 */
	private $applyUpdate;

	/**
	 * @var CheckUpdate
	 */
	private $checkUpdate;

	/**
	 * @param GitHubFunctions $gitHubFunctions
	 * @param ApplyUpdate     $apply
	 * @param GitRequest      $gitRequest
	 */
	public function __construct(
		ApplyUpdate $applyUpdate,
		CheckUpdate $checkUpdate
	) {
		$this->applyUpdate = $applyUpdate;
		$this->checkUpdate = $checkUpdate;
	}

	/**
	 * Return if up to date or the number of commits behind
	 * This invalidates the cache for the url.
	 *
	 * @return string
	 */
	public function check()
	{
		try {
			return Response::json($this->checkUpdate->getText());
		} catch (Exception $e) {
			return Response::error($e->getMessage()); // Not master
		}
	}

	/**
	 * This requires a php to have a shell access.
	 * This method execute the update (git pull).
	 *
	 * @return array|string
	 */
	public function apply()
	{
		try {
			$this->checkUpdate->canUpdate();
			// @codeCoverageIgnoreStart
		} catch (Exception $e) {
			return Response::error($e->getMessage());
		}
		// @codeCoverageIgnoreEnd

		// @codeCoverageIgnoreStart
		return $this->applyUpdate->run();
	}
}
