<?php
namespace BlueSpice\Reminder\Tests;

use BlueSpice\Reminder\RepeatingReminderDateCalculator;
use DateTime;
use MediaWikiIntegrationTestCase;

class RepeatingReminderDateCalculatorTest extends MediaWikiIntegrationTestCase {
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

		$this->assertEquals( '2020-05-15', $startDate->format( 'Y-m-d' ) );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-13' );
		$repeatConfig = (object)[
			'intervalType' => 'w',
			'intervalValue' => 1,
			'weekdaysToRepeat' => [ 3, 6 ]
		];
		$startDate = $dateCalculator->getStartDate( $currentDate, $repeatConfig );

		$this->assertEquals( '2020-05-13', $startDate->format( 'Y-m-d' ) );
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
		$this->assertEquals( '2020-05-08', $nextDate->format( 'Y-m-d' ) );

		// weekly interval
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-05' );
		$repeatConfig = (object)[
			'intervalType' => 'w',
			'intervalValue' => 1,
			'weekdaysToRepeat' => [ 0, 1, 2 ]
		];

		$nextDate = $dateCalculator->getNextReminderDateFromGivenDate( $currentDate, $repeatConfig );
		// sunday
		$this->assertEquals( '2020-05-10', $nextDate->format( 'Y-m-d' ) );

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
		$this->assertEquals( '2020-05-02', $nextDate->format( 'Y-m-d' ) );

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
		$this->assertEquals( '2020-07-12', $nextDate->format( 'Y-m-d' ) );

		// yearly interval
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-29' );
		$repeatConfig = (object)[
			'intervalType' => 'y',
			'intervalValue' => 1
		];
		$nextDate = $dateCalculator->getNextReminderDateFromGivenDate( $currentDate, $repeatConfig );
		$this->assertEquals( '2021-05-29', $nextDate->format( 'Y-m-d' ) );
	}

	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetNextDateForDailyInterval() {
		$dateCalculator = new RepeatingReminderDateCalculator();
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-06' );
		$nextDate = $dateCalculator->getNextDateForDailyInterval( $currentDate, 2 );

		$this->assertEquals( '2020-05-08', $nextDate->format( 'Y-m-d' ) );

		$nextDate = $dateCalculator->getNextDateForDailyInterval( $nextDate, 2 );
		$this->assertEquals( '2020-05-10', $nextDate->format( 'Y-m-d' ) );

		$nextDate = $dateCalculator->getNextDateForDailyInterval( $nextDate, 60 );
		$this->assertEquals( '2020-07-09', $nextDate->format( 'Y-m-d' ) );

		$nextDate = $dateCalculator->getNextDateForDailyInterval( $nextDate, 60 );
		$this->assertEquals( '2020-09-07', $nextDate->format( 'Y-m-d' ) );

		$nextDate = $dateCalculator->getNextDateForDailyInterval( $nextDate, 1 );
		$this->assertEquals( '2020-09-08', $nextDate->format( 'Y-m-d' ) );
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
		$this->assertEquals( '2020-05-10', $nextDate->format( 'Y-m-d' ) );

		// monday
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 1, $weekdaysToRepeat );
		$this->assertEquals( '2020-05-11', $nextDate->format( 'Y-m-d' ) );

		// tuesday
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 1, $weekdaysToRepeat );
		$this->assertEquals( '2020-05-12', $nextDate->format( 'Y-m-d' ) );

		// sunday
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 1, $weekdaysToRepeat );
		$this->assertEquals( '2020-05-17', $nextDate->format( 'Y-m-d' ) );

		/**
		 *  repeat every 2 weeks on tuesday, wednesday and friday
		 */
		$weekdaysToRepeat = [ 2, 3, 5 ];
		// current day is friday so the next date will be on tuesday after 2 weeks
		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-08' );

		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $currentDate, 2, $weekdaysToRepeat );
		$this->assertEquals( '2020-05-19', $nextDate->format( 'Y-m-d' ) );
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 2, $weekdaysToRepeat );
		$this->assertEquals( '2020-05-20', $nextDate->format( 'Y-m-d' ) );
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 2, $weekdaysToRepeat );
		$this->assertEquals( '2020-05-22', $nextDate->format( 'Y-m-d' ) );
		$nextDate = $dateCalculator->getNextDateForWeeklyInterval( $nextDate, 2, $weekdaysToRepeat );
		$this->assertEquals( '2020-06-02', $nextDate->format( 'Y-m-d' ) );
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
		$this->assertEquals( '2020-05-02', $nextDate->format( 'Y-m-d' ) );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-06' );
		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$currentDate,
			1,
			// first
			1,
			// saturday
			6
		);
		$this->assertEquals( '2020-06-06', $nextDate->format( 'Y-m-d' ) );

		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$nextDate,
			1,
			// first
			1,
			// saturday
			6
		);
		$this->assertEquals( '2020-07-04', $nextDate->format( 'Y-m-d' ) );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-01' );
		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$currentDate,
			3,
			// last
			-1,
			// tuesday
			2
		);
		$this->assertEquals( '2020-05-26', $nextDate->format( 'Y-m-d' ) );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-26' );
		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$currentDate,
			3,
			// last
			-1,
			// tuesday
			2
		);
		$this->assertEquals( '2020-08-25', $nextDate->format( 'Y-m-d' ) );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-16' );
		$nextDate = $dateCalculator->getNextDateForMonthlyDayOfWeekInterval(
			$currentDate,
			1,
			// third
			3,
			// saturday
			6
		);
		$this->assertEquals( '2020-06-20', $nextDate->format( 'Y-m-d' ) );
	}

	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetNextDateForMonthlyInterval() {
		$dateCalculator = new RepeatingReminderDateCalculator();

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-12' );
		$nextDate = $dateCalculator->getNextDateForMonthlyInterval( $currentDate, 1 );
		$this->assertEquals( '2020-06-12', $nextDate->format( 'Y-m-d' ) );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-12' );
		$nextDate = $dateCalculator->getNextDateForMonthlyInterval( $currentDate, 2 );
		$this->assertEquals( '2020-07-12', $nextDate->format( 'Y-m-d' ) );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2019-12-28' );
		$nextDate = $dateCalculator->getNextDateForMonthlyInterval( $currentDate, 2 );
		$this->assertEquals( '2020-02-28', $nextDate->format( 'Y-m-d' ) );

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2019-12-31' );
		$nextDate = $dateCalculator->getNextDateForMonthlyInterval( $currentDate, 2 );
		$this->assertEquals( '2020-03-31', $nextDate->format( 'Y-m-d' ) );
	}

	/**
	 * @covers BlueSpice\Reminder\RepeatingReminderDateCalculator
	 */
	public function testGetNextDateForYearlyInterval() {
		$dateCalculator = new RepeatingReminderDateCalculator();

		$currentDate = DateTime::createFromFormat( 'Y-m-d', '2020-05-06' );
		$nextDate = $dateCalculator->getNextDateForYearlyInterval( $currentDate, 1 );
		$this->assertEquals( '2021-05-06', $nextDate->format( 'Y-m-d' ) );

		$nextDate = $dateCalculator->getNextDateForYearlyInterval( $nextDate, 2 );
		$this->assertEquals( '2023-05-06', $nextDate->format( 'Y-m-d' ) );
	}
}
