<?php
namespace BlueSpice\Reminder\Tests;

use BlueSpice\Reminder\RepeatingReminderDateCalculator;
use DateTime;
use MediaWikiTestCase;

class RepeatingReminderDateCalculatorTest extends MediaWikiTestCase {
	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetStartDate() {
		$dateCalculator = new RepeatingReminderDateCalculator();

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-13' );
		$repeatConfig = (object)[
			'intervalType' => 'w',
			'intervalValue' => 1,
			'weekdaysToRepeat' => [ 5, 6 ]
		];
		$startDate = $dateCalculator->getStartDate( $currentDate, $repeatConfig );

		$this->assertEquals( $startDate->format( 'Y-m-d' ), '2020-05-15' );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-13' );
		$repeatConfig = (object)[
			'intervalType' => 'w',
			'intervalValue' => 1,
			'weekdaysToRepeat' => [ 3, 6 ]
		];
		$startDate = $dateCalculator->getStartDate( $currentDate, $repeatConfig );

		$this->assertEquals( $startDate->format( 'Y-m-d' ), '2020-05-13' );
	}

	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetNextReminderDateFromGivenDate() {
		$dateCalculator = new RepeatingReminderDateCalculator();

		// daily interval
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-06' );
		$repeatConfig = (object)[
			'intervalType' => 'd',
			'intervalValue' => 2
		];
		$nextDate = $dateCalculator->getNextReminderDateFromGivenDate( $currentDate, $repeatConfig );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-08' );

		// weekly interval
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-05' );
		$repeatConfig = (object)[
			'intervalType' => 'w',
			'intervalValue' => 1,
			'weekdaysToRepeat' => [ 0, 1, 2 ]
		];

		$nextDate = $dateCalculator->getNextReminderDateFromGivenDate( $currentDate, $repeatConfig );
		// sunday
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-10' );

		// monthly interval for certain weekdays
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-01' );
		$repeatConfig = (object)[
			'intervalType' => 'm',
			'intervalValue' => 1,
			'monthlyRepeatInterval' => (object)[
				'type' => 'dayOfTheWeek',
				'weekOrder' => 1,
				'weekdayOrder' => 6
			]
		];
		$nextDate = $dateCalculator->getNextReminderDateFromGivenDate( $currentDate, $repeatConfig );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-02' );

		// monthly interval
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-12' );
		$repeatConfig = (object)[
			'intervalType' => 'm',
			'intervalValue' => 2,
			'monthlyRepeatInterval' => (object)[
				'type' => 'dayOfTheMonth'
			]
		];
		$nextDate = $dateCalculator->getNextReminderDateFromGivenDate( $currentDate, $repeatConfig );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-07-12' );

		// yearly interval
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-29' );
		$repeatConfig = (object)[
			'intervalType' => 'y',
			'intervalValue' => 1
		];
		$nextDate = $dateCalculator->getNextReminderDateFromGivenDate( $currentDate, $repeatConfig );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2021-05-29' );
	}

	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetNextDateForDailyInterval() {
		$dateCalculator = new RepeatingReminderDateCalculator();
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-06' );
		$nextDate = $dateCalculator->getNextDateForDailyInterval( $currentDate, 2 );

		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-08' );

		$nextDate = $dateCalculator->getNextDateForDailyInterval( $nextDate, 2 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-10' );

		$nextDate = $dateCalculator->getNextDateForDailyInterval( $nextDate, 60 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-07-09' );

		$nextDate = $dateCalculator->getNextDateForDailyInterval( $nextDate, 60 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-09-07' );

		$nextDate = $dateCalculator->getNextDateForDailyInterval( $nextDate, 1 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-09-08' );
	}

	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetNextDateForWeeklyInterval() {
		$dateCalculator = new RepeatingReminderDateCalculator();
		$weekdaysToRepeat = [ 0, 1, 2 ];
		// current day is tuesday
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-05' );

		/**
		 * weekly repeat on sunday, monday and tuesday
		 */
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $currentDate, 1, $weekdaysToRepeat );
		// sunday
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-10' );

		// monday
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 1, $weekdaysToRepeat );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-11' );

		// tuesday
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 1, $weekdaysToRepeat );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-12' );

		// sunday
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 1, $weekdaysToRepeat );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-17' );

		/**
		 *  repeat every 2 weeks on tuesday, wednesday and friday
		 */
		$weekdaysToRepeat = [ 2, 3, 5 ];
		// current day is friday so the next date will be on tuesday after 2 weeks
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-08' );

		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $currentDate, 2, $weekdaysToRepeat );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-19' );
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 2, $weekdaysToRepeat );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-20' );
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 2, $weekdaysToRepeat );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-22' );
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 2, $weekdaysToRepeat );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-06-02' );
	}

	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetNextDateForMonthlyDayOfWeekInterval() {
		$dateCalculator = new RepeatingReminderDateCalculator();

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-01' );
		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$currentDate,
			1,
			// first
			1,
			// saturday
			6
		);
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-02' );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-06' );
		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$currentDate,
			1,
			// first
			1,
			// saturday
			6
		);
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-06-06' );

		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$nextDate,
			1,
			// first
			1,
			// saturday
			6
		);
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-07-04' );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-01' );
		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$currentDate,
			3,
			// last
			-1,
			// tuesday
			2
		);
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-05-26' );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-26' );
		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$currentDate,
			3,
			// last
			-1,
			// tuesday
			2
		);
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-08-25' );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-16' );
		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$currentDate,
			1,
			// third
			3,
			// saturday
			6
		);
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-06-20' );
	}

	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetNextDateForMonthlyInterval() {
		$dateCalculator = new RepeatingReminderDateCalculator();

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-12' );
		$nextDate = $dateCalculator->getNextDateForMonthlyInterval( $currentDate, 1 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-06-12' );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-12' );
		$nextDate = $dateCalculator->getNextDateForMonthlyInterval( $currentDate, 2 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-07-12' );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2019-12-28' );
		$nextDate = $dateCalculator->getNextDateForMonthlyInterval( $currentDate, 2 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-02-28' );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2019-12-31' );
		$nextDate = $dateCalculator->getNextDateForMonthlyInterval( $currentDate, 2 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2020-03-31' );
	}

	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetNextDateForYearlyInterval() {
		$dateCalculator = new RepeatingReminderDateCalculator();

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-06' );
		$nextDate = $dateCalculator->getNextDateForYearlyInterval( $currentDate, 1 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2021-05-06' );

		$nextDate = $dateCalculator->getNextDateForYearlyInterval( $nextDate, 2 );
		$this->assertEquals( $nextDate->format( 'Y-m-d' ), '2023-05-06' );
	}
}
