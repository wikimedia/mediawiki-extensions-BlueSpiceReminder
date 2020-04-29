<?php

namespace BlueSpice\Reminder;

use DateTime;

class RepeatingReminderDateCalculator {

	public const INTERVAL_TYPE_DAILY = 'd';
	public const INTERVAL_TYPE_WEEKLY = 'w';
	public const INTERVAL_TYPE_MONTHLY = 'm';
	public const INTERVAL_TYPE_YEARLY = 'y';

	public const MONTHLY_INTERVAL_DAY_OF_MONTH = 'dayOfTheMonth';
	public const MONTHLY_INTERVAL_DAY_OF_WEEK = 'dayOfTheWeek';

	private $dayNames = [
		0 => 'sunday',
		1 => 'monday',
		2 => 'tuesday',
		3 => 'wednesday',
		4 => 'thursday',
		5 => 'friday',
		6 => 'saturday'
	];

	private $ordinals = [
		-1 => 'last',
		1 => 'first',
		2 => 'second',
		3 => 'third',
		4 => 'fourth'
	];

	/**
	 * @param DateTime $givenDate
	 * @param \StdClass $repeatConfig
	 * @return DateTime
	 */
	public function getStartDate( DateTime $givenDate, \StdClass $repeatConfig ) {
		$startDate = $givenDate;
		if ( $repeatConfig->intervalType === self::INTERVAL_TYPE_WEEKLY ) {
			$givenDateDayOfTheWeek = $givenDate->format( 'w' );
			if ( !in_array( $givenDateDayOfTheWeek, $repeatConfig->weekdaysToRepeat ) ) {
				$startDate = $this->getNextDateForWeeklyInterval(
					$givenDate,
					$repeatConfig->intervalValue,
					$repeatConfig->weekdaysToRepeat
				);
			}
		}
		return $startDate;
	}

	/**
	 * @param DateTime $givenDate
	 * @param \StdClass $repeatConfig
	 * @return bool|DateTime
	 */
	public function getNextReminderDateFromGivenDate( DateTime $givenDate, \StdClass $repeatConfig ) {
		$nextDate = false;
		switch ( $repeatConfig->intervalType ) {
			case self::INTERVAL_TYPE_DAILY:
				$nextDate = $this->getNextDateForDailyInterval( $givenDate, $repeatConfig->intervalValue );
				break;
			case self::INTERVAL_TYPE_WEEKLY:
				$nextDate = $this->getNextDateForWeeklyInterval(
					$givenDate,
					$repeatConfig->intervalValue,
					$repeatConfig->weekdaysToRepeat
				);
				break;
			case self::INTERVAL_TYPE_MONTHLY:
				switch ( $repeatConfig->monthlyRepeatInterval->type ) {
					case self::MONTHLY_INTERVAL_DAY_OF_WEEK:
						$nextDate = $this->getNextDateForMonthlyDayOfWeekInterval(
							$givenDate,
							$repeatConfig->intervalValue,
							$repeatConfig->monthlyRepeatInterval->weekOrder,
							$repeatConfig->monthlyRepeatInterval->weekdayOrder
						);
						break;
					case self::MONTHLY_INTERVAL_DAY_OF_MONTH:
						$nextDate = $this->getNextDateForMonthlyInterval(
							$givenDate,
							$repeatConfig->intervalValue
						);
						break;
				}
				break;
			case self::INTERVAL_TYPE_YEARLY:
				$nextDate = $this->getNextDateForYearlyInterval( $givenDate, $repeatConfig->intervalValue );
				break;
		}

		return $nextDate;
	}

	/**
	 * @param DateTime $givenDate
	 * @param int $repeatEveryNDays
	 * @return DateTime
	 */
	public function getNextDateForDailyInterval( DateTime $givenDate, $repeatEveryNDays ) {
		$nextDate = clone $givenDate;
		$nextDate->modify( '+' . $repeatEveryNDays . ' day' );
		return $nextDate;
	}

	/**
	 * @param DateTime $givenDate
	 * @param int $repeatEveryNWeeks
	 * @param array $weekdaysToRepeat
	 * @return DateTime
	 */
	public function getNextDateForWeeklyInterval( DateTime $givenDate,
		$repeatEveryNWeeks, $weekdaysToRepeat ) {
		$nextDate = clone $givenDate;
		$currentDayOfTheWeek = $givenDate->format( 'w' );
		sort( $weekdaysToRepeat );

		// we'll never hit not existed element in array
		$doubleweekdaysToRepeat = array_merge( $weekdaysToRepeat, $weekdaysToRepeat );

		$nextWeekDay = $this->dayNames[$weekdaysToRepeat[0]];

		for ( $i = count( $weekdaysToRepeat ) - 1; $i >= 0; $i-- ) {
			if ( $currentDayOfTheWeek >= $weekdaysToRepeat[$i] ) {
				$nextWeekDay = $this->dayNames[$doubleweekdaysToRepeat[$i + 1]];
				break;
			}
		}

		$nextDate->modify( 'next ' . $nextWeekDay );

		if ( $nextWeekDay === $this->dayNames[$weekdaysToRepeat[0]] ) {
			$nextDate->modify( '+ ' . ( $repeatEveryNWeeks * 7 - 7 ) . ' days' );
		}

		return $nextDate;
	}

	/**
	 * @param DateTime $givenDate
	 * @param int $repeatEveryNMonths
	 * @param int $weekOrder
	 * @param int $weekdayOrder
	 * @return DateTime
	 */
	public function getNextDateForMonthlyDayOfWeekInterval( DateTime $givenDate, $repeatEveryNMonths,
		$weekOrder, $weekdayOrder ) {
		$nextDate = clone $givenDate;

		$nextDate->modify(
			$this->ordinals[$weekOrder] . ' ' . $this->dayNames[$weekdayOrder] . ' of this month'
		);

		if ( $givenDate->format( 'Y-m-d' ) >= $nextDate->format( 'Y-m-d' ) ) {

			for ( $i = 0; $i < $repeatEveryNMonths; $i++ ) {
				$nextDate->modify(
					$this->ordinals[$weekOrder] . ' ' . $this->dayNames[$weekdayOrder] . ' of next month'
				);
			}
		}

		return $nextDate;
	}

	/**
	 * @param DateTime $givenDate
	 * @param int $repeatEveryNMonths
	 * @return DateTime
	 */
	public function getNextDateForMonthlyInterval( DateTime $givenDate, $repeatEveryNMonths ) {
		$nextDate = clone $givenDate;
		$nextDate->modify( '+' . $repeatEveryNMonths . ' month' );
		if ( $givenDate->format( 'd' ) !== $nextDate->format( 'd' ) ) {
			return $this->getNextDateForMonthlyInterval( $givenDate, ++$repeatEveryNMonths );
		}
		return $nextDate;
	}

	/**
	 * @param DateTime $givenDate
	 * @param int $repeatEveryNYears
	 * @return DateTime
	 */
	public function getNextDateForYearlyInterval( DateTime $givenDate, $repeatEveryNYears ) {
		$nextDate = clone $givenDate;
		$nextDate->modify( '+' . $repeatEveryNYears . ' year' );
		return $nextDate;
	}

}
