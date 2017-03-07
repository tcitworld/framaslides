<?php
/**
 * Created by PhpStorm.
 * User: tcit
 * Date: 31/01/17
 * Time: 15:54
 */

namespace Strut\SlideBundle\Entity;


class Stats
{

	private $nbImages = 0;

	private $nbVideos = 0;

	private $nbFrames = 0;

	private $nbTextAreas = 0;

	private $nbShapes = 0;

	/**
	 * @return int
	 */
	public function getNbImages(): int
	{
		return $this->nbImages;
	}

	/**
	 * @param int $nbImages
	 */
	public function setNbImages(int $nbImages)
	{
		$this->nbImages = $nbImages;
	}

	public function increaseNbImages()
	{
		$this->nbImages++;
	}

	/**
	 * @return int
	 */
	public function getNbVideos(): int
	{
		return $this->nbVideos;
	}

	/**
	 * @param int $nbVideos
	 */
	public function setNbVideos(int $nbVideos)
	{
		$this->nbVideos = $nbVideos;
	}

	public function increaseNbVideos()
	{
		$this->nbVideos++;
	}

	/**
	 * @return int
	 */
	public function getNbFrames(): int
	{
		return $this->nbFrames;
	}

	/**
	 * @param int $nbFrames
	 */
	public function setNbFrames(int $nbFrames)
	{
		$this->nbFrames = $nbFrames;
	}

	public function increaseNbFrames()
	{
		$this->nbFrames++;
	}

	/**
	 * @return int
	 */
	public function getNbTextAreas(): int
	{
		return $this->nbTextAreas;
	}

	/**
	 * @param int $nbTextAreas
	 */
	public function setNbTextAreas(int $nbTextAreas)
	{
		$this->nbTextAreas = $nbTextAreas;
	}

	public function increaseNbTextAreas()
	{
		$this->nbTextAreas++;
	}

	/**
	 * @return int
	 */
	public function getNbShapes(): int
	{
		return $this->nbShapes;
	}

	/**
	 * @param int $nbShapes
	 */
	public function setNbShapes(int $nbShapes)
	{
		$this->nbShapes = $nbShapes;
	}

	public function increaseNbShapes()
	{
		$this->nbShapes++;
	}
}