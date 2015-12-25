<div style="width: 1020px; overflow: hidden; margin: 0 auto">
    <div style="width:500px; float: left; margin-right: 10px;">
        <h2>Count by date</h2>
        <div>
            <canvas id="canvas" height="200" width="500"></canvas>
        </div>
    </div>
    <div style="width:500px; float: left">
        <h2>Duration by date</h2>
        <div>
            <canvas id="canvas2" height="200" width="500"></canvas>
        </div>
    </div>
</div>
<script>
    var data = {
        labels :  <?php echo json_encode($distinctDates); ?>,
        datasets : <?php echo json_encode($chartObjects); ?>
    };
    var data2 = {
        labels :  <?php echo json_encode($distinctDates); ?>,
        datasets : <?php echo json_encode($chartObjects2); ?>
    };
    window.onload = function(){
        var ctx = document.getElementById("canvas").getContext("2d");
        var barChart = new Chart(ctx).Bar(data, {});
        var ctx2 = document.getElementById("canvas2").getContext("2d");
        var barChart2 = new Chart(ctx2).Bar(data2, {});

    };
</script>

<?php
echo CHtml::link('Update data', Yii::app()->baseUrl . '/YiiSlowQueryParser/default/update');

$dataProvider=new CActiveDataProvider('SlowQueryParser');
$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$dataProvider,
    'columns'=>array(
        'execution_time',
        'db_user',
        'db_host',
        'duration',
        'rows_examined',
        'rows_sent',
        array(
            'name'=>'query_text',
            'type'=>'raw',
            'value'=>'nl2br($data->query_text)',
        )
    )
));
?>