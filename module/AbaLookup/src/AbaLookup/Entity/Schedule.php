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
	Doctrine\ORM\Mapping\OneToOne,
	Doctrine\ORM\Mapping\Table,
	InvalidArgumentException
;

/**
 * @Entity
 * @Table(name = "schedules")
 *
 * A user's schedule
 */
class Schedule
{
	/**
	 * The mapping from day integers to day names
	 */
	protected static $week = [
		0 => 'Monday',
		1 => 'Tuesday',
		2 => 'Wednesday',
		3 => 'Thursday',
		4 => 'Friday',
		5 => 'Saturday',
		6 => 'Sunday',
	];

	/**
	 * @Id
	 * @Column(type = "integer")
	 * @GeneratedValue
	 *
	 * A unique identifier
	 */
	protected $id;

	/**
	 * @OneToOne(targetEntity = "User")
	 *
	 * The user to whom the schedule belongs
	 */
	protected $user;

	/**
	 * @Column(type = "boolean")
	 *
	 * Whether the schedule is active
	 */
	protected $enabled;

	/**
	 * @ManyToMany(targetEntity = "ScheduleDay", cascade = {"all"}, fetch = "EAGER")
	 * @JoinTable(
	 *     name = "schedule_day",
	 *     joinColumns = {@JoinColumn(name = "schedule_id", referencedColumnName = "id")},
	 *     inverseJoinColumns = {@JoinColumn(name = "day_id", referencedColumnName = "id", unique = TRUE)}
	 * )
	 *
	 * The days in schedule
	 *
	 * Note the name of the join table is the singular version of the name
	 * of the table for the ScheduleDay entities.
	 */
	protected $days;

	/**
	 * Constructor
	 *
	 * Creates a new Schedule object and populates it.
	 *
	 * @param User $user The user to whom the schedule belongs.
	 * @param bool $enabled Is the schedule active?
	 * @throws InvalidArgumentException
	 */
	public function __construct(User $user, $enabled = TRUE)
	{
		if (!isset($enabled) || !is_bool($enabled)) {
			throw new InvalidArgumentException();
		}
		$this->user    = $user;
		$this->enabled = $enabled;
		$this->days    = new ArrayCollection();
		// Add the days to the schedule
		foreach (self::$week as $day => $name) {
			$scheduleDay = new ScheduleDay($day, $name);
			$this->days->set($day, $scheduleDay);
		}
	}

	/**
	 * Sets the availability of the given interval of time
	 *
	 * @param int $day The day on which the time interval lies.
	 * @param int $startTime The start time for the interval.
	 * @param int $endTime The end time for the interval.
	 * @param bool $available Whether the specified interval is to be marked as available.
	 * @throws InvalidArgumentException
	 * @return $this
	 */
	public function setAvailability($day, $startTime, $endTime, $available)
	{
		if (
			   !isset($day, $startTime, $endTime, $available)
			|| !is_int($day)
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
		$scheduleDay = $this->days->get($day);
		$scheduleDay->setAvailability($startTime, $endTime, $available);
		return $this;
	}

	/**
	 * Enables the schedule
	 *
	 * @return $this
	 */
	public function enable()
	{
		$this->enabled = TRUE;
		return $this;
	}

	/**
	 * Disables the schedule
	 *
	 * @return $this
	 */
	public function disable()
	{
		$this->enabled = FALSE;
		return $this;
	}

	/**
	 * Returns the week for the schedule
	 *
	 * @return array
	 */
	public function getWeek()
	{
		return self::$week;
	}

	/**
	 * Returns the number of days in a week for the schedule
	 *
	 * @return int
	 */
	public function getNumberOfDays()
	{
		return count(self::$week);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns the user to whom the schedule belongs
	 *
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Returns whether the interval of time on the given day in the schedule is available
	 *
	 * Return whether the interval of time from {@code $startTime} to {@code $endTime}
	 * on {@code $day} is set as available.
	 *
	 * @param int $day The day on which the time interval lies.
	 * @param int $startTime The start time for the interval.
	 * @param int $endTime The end time for the interval.
	 * @throws InvalidArgumentException
	 * @return bool
	 */
	public function isAvailable($day, $startTime, $endTime)
	{
		if (
			   !isset($day, $startTime, $endTime)
			|| !is_int($day)
			|| !is_int($startTime)
			|| !is_int($endTime)
		) {
			throw new InvalidArgumentException();
		}
		if ($startTime >= $endTime) {
			throw new InvalidArgumentException(sprintf(
				'The end time must be be greater than the start time.'
			));
		}
		$scheduleDay = $this->days->get($day);
		return $scheduleDay->isAvailable($startTime, $endTime);
	}

	/**
	 * Returns whether the schedule is enabled
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * Returns the days in the schedule
	 *
	 * @return ArrayCollection
	 */
	public function getDays()
	{
		return $this->days;
	}
}
