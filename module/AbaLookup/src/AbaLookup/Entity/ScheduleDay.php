<?php

namespace AbaLookup\Entity;

use
	Doctrine\Common\Collections\ArrayCollection,
	Doctrine\ORM\Mapping\Column,
	Doctrine\ORM\Mapping\Entity,
	Doctrine\ORM\Mapping\GeneratedValue,
	Doctrine\ORM\Mapping\Id,
	Doctrine\ORM\Mapping\JoinColumn,
	Doctrine\ORM\Mapping\JoinTable,
	Doctrine\ORM\Mapping\ManyToMany,
	Doctrine\ORM\Mapping\Table,
	InvalidArgumentException
;

/**
 * @Entity
 * @Table(name = "schedule_days")
 *
 * A day in a schedule
 */
class ScheduleDay
{
	/**
	 * The number of hours in a day
	 */
	const HOURS_DAY = 24;

	/**
	 * The number of minutes in half a hour
	 */
	const MINUTES_HALF_HOUR = 30;

	/**
	 * The number of minutes in a hour
	 */
	const MINUTES_HOUR = 60;

	/**
	 * The number used to convert a hour to military time
	 * (e.g. 1hr into the day => 100 in military time)
	 */
	const MILITARY_TIME = 100;

	/**
	 * @Id
	 * @Column(type = "integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @Column(type = "integer")
	 *
	 * The integer representation of the day
	 */
	protected $day;

	/**
	 * @Column(type = "string", length = 16)
	 *
	 * The name of the day
	 */
	protected $name;

	/**
	 * @Column(type = "string", length = 3)
	 *
	 * The abbreviated name of the day
	 */
	protected $abbreviation;

	/**
	 * @ManyToMany(targetEntity = "ScheduleInterval", cascade = {"all"}, fetch = "EAGER")
	 * @JoinTable(
	 *     name = "schedule_interval",
	 *     joinColumns = {@JoinColumn(name = "day_id", referencedColumnName = "id")},
	 *     inverseJoinColumns = {@JoinColumn(name = "interval_id", referencedColumnName = "id", unique = TRUE)}
	 * )
	 *
	 * The intervals that exist in the day
	 *
	 * Note the name of the JoinTable is the singular version
	 * of the name of the table for ScheduleInterval.
	 */
	protected $intervals;

	/**
	 * @Column(type = "integer", name = "interval_minutes")
	 *
	 * The number of minutes of an interval
	 *
	 * The length of all the intervals in the day are the same
	 * thus the interval length is defined here instead of inside the
	 * intervals themselves.
	 */
	protected $intervalMinutes;

	/**
	 * Constructor
	 *
	 * Create a new ScheduleDay and fill it with the appropriate intervals.
	 *
	 * @param int $day The integer representation of the day.
	 * @param string $name The name of the day.
	 * @param int $hours The number of hours in the day.
	 * @param int $intervalMinutes The number of minutes in a interval on this day.
	 * @throws InvalidArgumentException
	 */
	public function __construct($day, $name, $hours = self::HOURS_DAY, $intervalMinutes = self::MINUTES_HALF_HOUR)
	{
		if (!isset($day, $name, $hours, $intervalMinutes)) {
			throw new InvalidArgumentException();
		}
		if (
			   !is_int($day)
			|| !is_string($name)
			|| !$name
			|| !is_int($hours)
			|| ($hours <= 0)
			|| !is_int($intervalMinutes)
			|| ($intervalMinutes <= 0)
		) {
			throw new InvalidArgumentException();
		}
		$this->day             = $day;
		$this->name            = $name;
		$this->abbreviation    = substr($name, 0, 3);
		$this->intervals       = new ArrayCollection();
		$this->intervalMinutes = $intervalMinutes; // The number of minutes in an interval
		// Create and add intervals to the day
		$numberOfIntervalsPerHr = self::MINUTES_HOUR / $intervalMinutes; // The number of intervals each hr
		$hoursMilitary = $hours * self::MILITARY_TIME; // Hours in a day (in military time format)
		// For each hour in a day
		for ($hour = 0; $hour < $hoursMilitary; $hour += self::MILITARY_TIME) {
			// For each interval in each the hour
			for ($i = 0; $i < $numberOfIntervalsPerHr; $i++) {
				// Calculate the start and end times (in military format)
				$startTime = $hour + ($i * $intervalMinutes);
				$endMinute = ((($i + 1) * $intervalMinutes) % self::MINUTES_HOUR);
				$endTime = $hour + (($endMinute == 0) ? self::MILITARY_TIME : $endMinute);
				// Create a new interval and add it to the day
				$interval = new ScheduleInterval($startTime, $endTime);
				$this->intervals->add($interval);
			}
		}
	}

	/**
	 * Overrides the default name for the day
	 *
	 * @param string $name The name of the day.
	 * @throws InvalidArgumentException
	 * @return $this
	 */
	public function setName($name)
	{
		if (!isset($name) || !is_string($name) || !$name) {
			throw new InvalidArgumentException();
		}
		$this->name = $name;
		return $this;
	}

	/**
	 * Overrides the default abbreviation for the day
	 *
	 * @param string $abbreviation The abbreviated name of the day.
	 * @throws InvalidArgumentException
	 * @return $this
	 */
	public function setAbbrev($abbreviation)
	{
		if (!isset($abbreviation) || !is_string($abbreviation) || !$abbreviation) {
			throw new InvalidArgumentException();
		}
		$this->abbreviation = $abbreviation;
		return $this;
	}

	/**
	 * Sets the availability of the interval with the given start and end times
	 *
	 * The start and end times given may span multiple intervals.
	 *
	 * @param int $startTime The start time of the interval which is having its availability set.
	 * @param int $endTime The end time of the interval which is having its availability set.
	 * @param bool $available Whether the strech of time from the start time to the end time is available or not.
	 * @throws InvalidArgumentException
	 * @return $this
	 */
	public function setAvailability($startTime, $endTime, $available)
	{
		if (
			   !isset($startTime, $endTime, $available)
			|| !is_int($startTime)
			|| !is_int($endTime)
			|| !is_bool($available)
		) {
			throw new InvalidArgumentException();
		}
		if ($startTime >= $endTime) {
			throw new InvalidArgumentException(sprintf(
				'The end time must be be greater than the start time.'
			));
		}
		foreach ($this->intervals as $interval) {
			if ($interval->getStartTime() >= $startTime && $interval->getEndTime() <= $endTime) {
				$interval->setAvailability($available);
			}
		}
		return $this;
	}

	/**
	 * Returns the integer representation of the day
	 *
	 * @return int
	 */
	public function getDay()
	{
		return $this->day;
	}

	/**
	 * Returns the name of the day
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the abbreviated name of the day
	 *
	 * @return string
	 */
	public function getAbbrev()
	{
		return $this->abbreviation;
	}

	/**
	 * Returns the availability of the interval with the given start and end times
	 *
	 * If the start and end times span multiple intervals, TRUE will be returned if
	 * and only if all intervals in the time are marked as available. If the start
	 * and end times corresponds to a single interval, the interval's availability is returned.
	 *
	 * @param int $startTime The start time of the interval which is having its availability checked.
	 * @param int $endTime The end time of the interval which is having its availability checked.
	 * @throws InvalidArgumentException
	 * @return bool
	 */
	public function isAvailable($startTime, $endTime)
	{
		if (!isset($startTime, $endTime) || !is_int($startTime) || !is_int($endTime)) {
			throw new InvalidArgumentException();
		}
		if ($startTime >= $endTime) {
			throw new InvalidArgumentException(sprintf(
				'The end time must be be greater than the start time.'
			));
		}
		$available = TRUE;
		foreach ($this->intervals as $interval) {
			if ($interval->getStartTime() >= $startTime && $interval->getEndTime() <= $endTime) {
				$available = $available && $interval->isAvailable();
			}
		}
		return $available;
	}

	/**
	 * Returns the number of minutes in an interval for the day
	 *
	 * @return int
	 */
	public function getIntervalMinutes()
	{
		return $this->intervalMinutes;
	}

	/**
	 * Returns the ArrayCollection of intervals for the day
	 *
	 * @return ArrayCollection
	 */
	public function getIntervals()
	{
		return $this->intervals;
	}
}
