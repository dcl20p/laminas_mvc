<?php
namespace Zf\Ext\Resource;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class ControllerResource extends AbstractPlugin
{
	/**
	 * Types of paths.
	 * 
	 * @var string
	 */
	const TYPE_UPLOAD_USER = 'UPLOAD_USER';
	const TYPE_UPLOAD_SYSTEM = 'UPLOAD_SYSTEM';
	const TYPE_CACHE = 'CACHE';

	/**
	 * Resources instance.
	 * 
	 * @var ControllerResource|null
	 */
	protected static $_instance;

	/**
	 * Path variables.
	 * 
	 * @var string
	 */
	protected $_publicPath;
	protected $_skinDirectory;
	protected $_uploadDirectory;
	protected $_cacheDirectory;
	protected $_siteName;
	protected $_types;

	/**
	 * Set the public path.
	 *
	 * @param string $publicPath
	 */
	public function setPublicPath(?string $publicPath): void
	{
		$this->_publicPath = $publicPath;
	}

	/**
	 * Get the public path.
	 * 
	 * @return string
	 */
	public function getPublicPath(): string
	{
		return $this->_publicPath;
	}
	/**
	 * Set the upload directory path.
	 *
	 * @param string $uploadDirectory
	 */
	public function setUploadDirectory(?string $uploadDirectory): void
	{
		$this->_uploadDirectory = $uploadDirectory;
	}

	/**
	 * Get upload directory path.
	 * 
	 * @return string
	 */
	public function getUploadDirectory(): ?string
	{
		return $this->_uploadDirectory;
	}

	/**
	 * Set the cache directory path.
	 *
	 * @param string $cacheDirectory
	 */
	public function setCacheDirectory(?string $cacheDirectory): void
	{
		$this->_cacheDirectory = $cacheDirectory;
	}

	/**
	 * Get cache directory path.
	 * 
	 * @return string
	 */
	public function getCacheDirectory(): ?string
	{
		return $this->_cacheDirectory;
	}

	/**
	 * Set the skin directory path.
	 *
	 * @param string $skin
	 */
	public function setSkinDirectory(?string $skin): void
	{
		$this->_skinDirectory = $skin;
	}

	/**
	 * Get skin directory path.
	 * 
	 * @return string
	 */
	public function getSkinDirectory(): ?string
	{
		return $this->_skinDirectory;
	}

	/**
	 * Set the site name.
	 *
	 * @param string $siteName
	 */
	public function setSiteName(?string $siteName): void
	{
		$this->_siteName = $siteName;
	}

	/**
	 * Get site name.
	 * 
	 * @return string
	 */
	public function getSiteName(): ?string
	{
		return $this->_siteName;
	}

	/**
	 * Set the types array.
	 *
	 * @param array $types
	 */
	public function setTypes(array $types): void
	{
		$this->_types = $types;
	}

	/**
	 * Get the types array.
	 * 
	 * @return array
	 */
	public function getTypes(): array
	{
		return $this->_types;
	}

	/**
	 * Get path file by type
	 *
	 * @access	private
	 * @param	string	$type	
	 * @return	mixed
	 */
	private function getType(string $type): mixed
	{
		$types = $this->getTypes();
		if (is_null($types[$type])) {
			throw new \Exception("Don`t supported type: {$type}", 500);
		}
		return $types[$type];
	
	}

	/**
	 * Get the instance.
	 * 
	 * @param array $options
	 * 
	 * @return ControllerResource
	 */
	public static function getInstance(array $options = []): ControllerResource
	{
		if (null === self::$_instance) {
			self::$_instance = new self();
			if ($options && is_array($options)) {
				foreach ($options as $key => $value) {
					$method = 'set' . ucfirst($key);
					if (method_exists(self::$_instance, $method)) {
						self::$_instance->{$method}($value);
					}
				}
			}
		}
		return self::$_instance;
	}

	/**
	 * Extract filenames.
	 * 
	 * @param string|array $filenames
	 * @param string $type
	 * @param bool $getSystemPath
	 * @param bool $useSite
	 * 
	 * @return string|array
	 */
	public function files($filenames, string $type = '', bool $getSystemPath = false, bool $useSite = false): string|array
	{
		if (is_string($filenames)) {
			if ((strpos($filenames, 'http://') === 0) || strpos($filenames, 'https://') === 0) {
				return $filenames;
			}
			return $this->normalizePath($this->getPathOfType($type, $getSystemPath, $useSite) . ($filenames ? "/{$filenames}" : ''));
		}
		if (is_array($filenames)) {
			$result = [];
			foreach ($filenames as $value) {
				$result[] = $this->files($value, $type, $getSystemPath, $useSite);
			}
			return $result;
		}
	}

	/**
	 * Normalize path.
	 * 
	 * @param mixed $path
	 * 
	 * @return mixed
	 */
	private function normalizePath($path)
	{
		return str_replace(['\\', '\\\\', '//', '///'], '/', $path);
	}

	/**
	 * Get path of files.
	 * 
	 * @param string $type
	 * @param bool $getSystemPath
	 * @param bool $useSite
	 * 
	 * @return string
	 */
	private function getPathOfType(string $type, bool $getSystemPath, bool $useSite): string
	{
		$publicPath = $getSystemPath ? $this->_publicPath : '';

		switch ($type) {
			case self::TYPE_UPLOAD_USER:
				return $publicPath . '/' . $this->getUploadDirectory();

				break;
			case self::TYPE_UPLOAD_SYSTEM:
				return realpath($publicPath . '/' . $this->getUploadDirectory() . '/..');

				break;
			case self::TYPE_CACHE:
				return realpath($publicPath . '/' . $this->getCacheDirectory());

				break;
			default:
				$opts = [
					'',
					'skin' => $this->getSkinDirectory(),
					'type' => $this->getType($type)
				];

				if ($useSite) {
					$opts['site'] = $this->getSiteName();
				}
				return implode('/', $opts);
				break;
		}
	}
}