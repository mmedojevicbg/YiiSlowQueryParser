<?php
class SlowQueryParser extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

	public function tableName()
	{
		return 'slow_query_parser';
	}

	public function rules()
	{
		return array(
			array('id, execution_time, db_user, db_host, duration, rows_examined, rows_sent, query_text', 'safe')
		);
	}

    public static function distinctDates() {
        $q = "SELECT DATE(execution_time)
              FROM slow_query_parser
              GROUP BY DATE(execution_time)
              ORDER BY DATE(execution_time) DESC
              LIMIT 14;";
        $result = Yii::app()->db->createCommand($q)->queryColumn();
        return array_reverse($result);
    }

    public static function queriesByDate() {
        $q = "SELECT COUNT(id) as cnt, sum(duration) as sum_duration, DATE(execution_time) as execution_date
              FROM slow_query_parser
              GROUP BY DATE(execution_time)
              ORDER BY DATE(execution_time) DESC
              LIMIT 14;";
        $result = Yii::app()->db->createCommand($q)->queryAll();
        return array_reverse($result);
    }
}
