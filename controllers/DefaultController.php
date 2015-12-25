<?php

class DefaultController extends Controller
{
    public $layout='layout';
	public function actionIndex()
	{
        Yii::app()->clientScript->registerCoreScript('jquery');
        $distinctDates = SlowQueryParser::distinctDates();
        $queriesByDate = SlowQueryParser::queriesByDate();
		$this->render('index', array('chartObjects' => $this->generateChartObjectsCount($queriesByDate),
                                     'chartObjects2' => $this->generateChartObjectsDuration($queriesByDate),
                                     'distinctDates' => $distinctDates));
	}

    public function actionUpdate()
    {
        $q = "TRUNCATE TABLE slow_query_parser;";
        Yii::app()->db->createCommand($q)->execute();
        $queries = $this->parseLogFile();
        foreach($queries as $query) {
            $model = new SlowQueryParser();
            $model->execution_time = $query['time'];
            $model->db_user = $query['user'];
            $model->db_host = $query['host'];
            $model->duration = $query['query_time'];
            $model->rows_examined = $query['rows_examined'];
            $model->rows_sent = $query['rows_sent'];
            $model->query_text = $query['sql'];
            $model->save();
        }
        $this->redirect(Yii::app()->baseUrl . '/YiiSlowQueryParser');
    }

    private function parseLogFile() {
        $queries = array();
        $query = array();
        $logFilePath = $this->getModule()->logFilePath;
        $handle = fopen($logFilePath, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = str_replace("\n", "", $line);
                if(strpos($line, '# Time') !== false) {
                    if(@$query['time']) {
                        $queries[] = $query;
                    }
                    $query = array();
                    $query['sql'] = '';
                    $matches = array();
                    preg_match('~Time\:\s([0-9]+)\s+([0-9]+)\:([0-9]+)\:([0-9]+)~', $line, $matches);
                    $query['time'] = date('Y-m-d H:i:s', mktime(
                        $matches[2],
                        $matches[3],
                        $matches[4],
                        substr($matches[1], 2, 2),
                        substr($matches[1], 4, 2),
                        substr($matches[1], 0, 2)
                    ));
                } elseif(strpos($line, '# User@Host') !== false) {
                    $matches = array();
                    preg_match('~User\@Host\:\s+(.+?)\[(.+?)\]\s+\@\s+\[(.*?)\]~', $line, $matches);
                    $query['user'] = $matches[1];
                    $query['host'] = $matches[3];
                } elseif(strpos($line, '# Query_time') !== false) {
                    $matches = array();
                    preg_match('~Query_time\:\s+(.+?)\s+Lock_time\:\s+(.+?)\s+Rows_sent\:\s+(.+?)\s+Rows_examined\:\s+(.+)~', $line, $matches);
                    $query['query_time'] = $matches[1];
                    $query['lock_time'] = $matches[2];
                    $query['rows_sent'] = $matches[3];
                    $query['rows_examined'] = $matches[4];
                } else {
                    $query['sql'] .= $line . "\n";
                }
            }
            fclose($handle);
        }
        return $queries;
    }

    private function generateChartObjectsCount($queriesByDate) {
        $objects = array();
        $object = new stdClass();
        $object->label = 'Count';
        $object->fillColor = 'rgba(220,220,220,0.2)';
        $object->strokeColor = 'rgba(220,220,220,1)';
        $object->pointColor = 'rgba(220,220,220,1)';
        $object->pointStrokeColor = '#fff';
        $object->pointHighlightFill = '#fff';
        $object->pointHighlightStroke = '#fff';
        $object->data = array();
        foreach($queriesByDate as $query) {
            $object->data[] = $query['cnt'];
        }
        $objects[] = $object;
        return $objects;
    }
    private function generateChartObjectsDuration($queriesByDate) {
        $objects = array();
        $object = new stdClass();
        $object->label = 'Duration';
        $object->fillColor = 'rgba(220,220,220,0.2)';
        $object->strokeColor = 'rgba(220,220,220,1)';
        $object->pointColor = 'rgba(220,220,220,1)';
        $object->pointStrokeColor = '#fff';
        $object->pointHighlightFill = '#fff';
        $object->pointHighlightStroke = '#fff';
        $object->data = array();
        foreach($queriesByDate as $query) {
            $object->data[] = $query['sum_duration'];
        }
        $objects[] = $object;
        return $objects;
    }
}