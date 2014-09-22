<?php
class Model_Home extends Model_Database
{
    public function getStatsData()
    {        
        $statsData = array();		
		$rows = $this->_db->query(Database::SELECT, 'SELECT hour, tweets FROM stats_hourly ORDER BY id DESC LIMIT 24')->as_array();
		if(!empty($rows))
		{
			$rows = array_reverse($rows);
			$sLables = array();
			$sValues = array();
			foreach($rows as $row)
			{
				$sLables[] = sprintf("%02d", $row['hour']+1);
				$sValues[] = $row['tweets'];
			}
			$statsData['tweets']['labels'] = '"' . implode('","', $sLables) . '"';
			$statsData['tweets']['values'] = implode(',', $sValues);
		}
		return $statsData;
    }
	
	public function getRecentWalls()
	{		
		$rows = $this->_db->query(Database::SELECT, 'SELECT wallname
			FROM stats_topwalls
			WHERE tweetsOverall > 5
			ORDER BY lastUpdate DESC
			LIMIT 25')->as_array(null, 'wallname');
		return $rows;		
	}
}