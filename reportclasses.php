<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * TQuiz Report Classes.
 *
 * @package    mod_tquiz
 * @copyright  2014 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer for tquiz reports.
 *
 * @package    mod_tquiz
 * @copyright  2014 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class mod_tquiz_base_report {

    protected $report="";
    protected $head=array();
	protected $rawdata=null;
    protected $fields = array();
	protected $dbcache=array();
	
	abstract function process_raw_data($formdata);
	abstract function fetch_formatted_heading();
	
	public function fetch_fields(){
		return $this->fields;
	}
	public function fetch_head(){
		$head=array();
		foreach($this->fields as $field){
			$head[]=get_string($field,'tquiz');
		}
		return $head;
	}
	public function fetch_name(){
		return $this->report;
	}


	public function fetch_cache($table,$rowid){
		global $DB;
		if(!array_key_exists($table,$this->dbcache)){
			$this->dbcache[$table]=array();
		}
		if(!array_key_exists($rowid,$this->dbcache[$table])){
			$this->dbcache[$table][$rowid]=$DB->get_record($table,array('id'=>$rowid));
		}
		return $this->dbcache[$table][$rowid];
	}

	public function fetch_time_difference($starttimestamp,$endtimestamp){
			
			//return empty string if the timestamps are not both present.
			if(!$starttimestamp || !$endtimestamp){return '';}
			
			$s = $date = new DateTime();
			$s->setTimestamp($starttimestamp);
						
			$e =$date = new DateTime();
			$e->setTimestamp($endtimestamp);
						
			$diff = $e->diff($s);
			$ret = $diff->format("%H:%I:%S");
			return $ret;
	}
	
	public function fetch_formatted_rows($withlinks=true){
		$records = $this->rawdata;
		$fields = $this->fields;
		$returndata = array();
		foreach($records as $record){
			$data = new stdClass();
			foreach($fields as $field){
				$data->{$field}=$this->fetch_formatted_field($field,$record,$withlinks);
			}//end of for each field
			$returndata[]=$data;
		}//end of for each record
		return $returndata;
	}
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'timecreated':
					$ret = date("Y-m-d H:i:s",$record->timecreated);
					break;
				case 'userid':
					$ret =fullname($DB->get_record('user',array('id'=>$record->userid)));
					break;
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
}

class mod_tquiz_attempt_report extends  mod_tquiz_base_report {
	
	protected $report="attempt";
	protected $fields = array('qname','timetaken','qplaycount','correct');	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'timetaken':
					if(!property_exists($record,'selectanswertime') || !property_exists($record,'revealanswerstime')){
						$ret="";
					}else{
						$ret = $this->fetch_time_difference($record->revealanswerstime,$record->selectanswertime);
					}
					break;
				case 'qname':
					if($record->questionid==0){
						$ret="Summary";
					}else{
						$thequestion = $this->fetch_cache('tquiz_questions',$record->questionid);
						$ret = $thequestion->name;
					}
					break;
				case 'qplaycount':
					if($record->questionid==0){
						$ret="";
					}else{
						$ret = $record->qplaycount;
					}
					break;
				case 'correct':
					if($record->questionid==0){
						$ret="";
					}else{
						$thequestion = $this->fetch_cache('tquiz_questions',$record->questionid);
						$correctanswer = $thequestion->correctanswer;

						if($record->selectanswer==$correctanswer){
							$ret =get_string('yes');
						}else{
							$ret=get_string('no');
						}
					}
					break;
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		$record = $this->headingdata;
		$ret='';
		if(!$record){return $ret;}
		
		if($record->questionid==0){
			$user = $this->fetch_cache('user',$record->userid);
			$attempt = $this->fetch_cache('tquiz_attempt',$record->attemptid);
			$tquiz = $this->fetch_cache('tquiz',$attempt->tquizid);
			$a = new stdClass();
			$a->tquizname = $tquiz->name;
			$a->username = fullname($user);
			$a->attemptdate = date("Y-m-d H:i:s",$attempt->timecreated);
			$ret = get_string('attemptheader','tquiz',$a);
		}
		return $ret;
	}
	
	public function process_raw_data($formdata){
		global $DB;
		$alldata = $DB->get_records('tquiz_attempt_log',array('userid'=>$formdata->userid,'attemptid'=>$formdata->attemptid),'questionid, timecreated'); 
		$questiondata = array();
		$currentq=-1;
		$thequestion = null;
		foreach($alldata as $adata){

		
		//if we have changed question
			//stash the last one and start building the next one
			if($adata->questionid!=$currentq){
					//stash the previous q if we had one
					if($thequestion){$questiondata[]=$thequestion;}
					
					//this indicates the heading field
					if($adata->questionid==0){
						$this->headingdata = new stdClass();
						$this->headingdata->questionid=$adata->questionid;
						$this->headingdata->attemptid=$adata->attemptid;
						$this->headingdata->userid=$adata->userid;
						continue;					
					}
					
					//init new row/question data object
					$thequestion = new stdClass();
					$thequestion->questionid=$adata->questionid;
					$thequestion->attemptid=$adata->attemptid;
					$thequestion->userid=$adata->userid;
					$thequestion->qplaycount=0;
					$thequestion->revealanswerstime=false;
					$thequestion->revealanswerstime_js=false;
					$thequestion->startplayquestiontime=false;
					$thequestion->startplayquestiontime_js=false;
					$thequestion->endplayquestiontime=false;
					$thequestion->endplayquestiontime_js=false;
					$thequestion->selectanswer=false;
					$thequestion->selectanswertime=false;
					$thequestion->selectanswertime_js=false;
					
					$currentq = $adata->questionid;
					//for now we disregard the 0 question events
					if($adata->questionid==0){continue;}
			}
				
			switch ($adata->eventkey){
				case 'startplayquestion':
					$thequestion->{$adata->eventkey . 'time'}=$adata->timecreated;
					$thequestion->{$adata->eventkey . 'time_js'}=$adata->eventtime;
					$thequestion->qplaycount++;
					break;
				case 'endplayquestion':
				case 'revealanswers':
					$thequestion->{$adata->eventkey . 'time'}=$adata->timecreated;
					$thequestion->{$adata->eventkey . 'time_js'}=$adata->eventtime;
					break;
				case 'selectanswer':
					$thequestion->{$adata->eventkey . 'time'}=$adata->timecreated;
					$thequestion->{$adata->eventkey . 'time_js'}=$adata->eventtime;
					$thequestion->{$adata->eventkey}=$adata->eventvalue;
					break;
				default:
					$thequestion->{$adata->eventkey}=$adata->eventvalue;
					break;
			}//end of switch
		}//end of for each
		//stash the final parsed question
		if($thequestion){
			$questiondata[]=$thequestion;
		}
		
		//At this point we have an event object per question from the log to process.
		//eg timetaken = $question->selectanswer - $question->endplayquestion;
		//need to make final and start "questions" have different ids (0 and 9999)
		$this->rawdata= $questiondata;
		return true;
	}

}

class mod_tquiz_questiondetails_report extends  mod_tquiz_base_report {
	
	protected $report="questiondetails";
	protected $fields = array('username','timetaken','qplaycount','correct');	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'timetaken':
						$ret = $this->fetch_time_difference($record->revealanswerstime,$record->selectanswertime);
						break;

				case 'username':
						$theuser = $this->fetch_cache('user',$record->userid);
						$ret = fullname($theuser);
					break;
				
				case 'qplaycount':
						$ret = $record->qplaycount;
					break;
					
				case 'correct':
						$thequestion = $this->fetch_cache('tquiz_questions',$record->questionid);
						$correctanswer = $thequestion->correctanswer;

						if($record->selectanswer==$correctanswer){
							$ret =get_string('yes');
						}else{
							$ret=get_string('no');
						}
					break;
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		$record = $this->headingdata;
		$ret='';
		if(!$record){return $ret;}
		$q = $this->fetch_cache('tquiz_questions',$record->questionid);
		return get_string('questiondetails','tquiz',$q->name);
		
	}
	
	public function process_raw_data($formdata){
		global $DB;
		
		//heading data is just qname really
		$this->headingdata = new stdClass();
		$this->headingdata->questionid=$formdata->questionid;
		
		//get all data for this question by user
		$sql =	"SELECT tal.*
		FROM {tquiz_attempt_log} tal
		INNER JOIN {tquiz_attempt} ta ON ta.id = tal.attemptid
		WHERE ta.status = 'current' AND tal.questionid=:talquestionid
		ORDER BY tal.userid";
		$params=array();
		$params['talquestionid'] = $formdata->questionid;
	
		
		$alldata = $DB->get_records_sql($sql,$params); 
		$currentuserid=-1;
		$theattempt=null;
		foreach($alldata as $adata){
			//if we have changed question
			//stash the last one and start building the next one
			if($adata->userid!=$currentuserid){
					//stash the previous q if we had one
					if($theattempt){$attemptdata[]=$theattempt;}
					
					//init new row/question data object
					$theattempt = new stdClass();
					$theattempt->questionid=$adata->questionid;
					$theattempt->attemptid=$adata->attemptid;
					$theattempt->userid=$adata->userid;
					$theattempt->qplaycount=0;
					$theattempt->revealanswerstime=false;
					$theattempt->revealanswerstime_js=false;
					$theattempt->startplayquestiontime=false;
					$theattempt->startplayquestiontime_js=false;
					$theattempt->endplayquestiontime=false;
					$theattempt->endplayquestiontime_js=false;
					$theattempt->selectanswer=false;
					$theattempt->selectanswertime=false;
					$theattempt->selectanswertime_js=false;
					
					$currentuserid = $adata->userid;

			}
			//get event log data into the attempt object
			switch ($adata->eventkey){
					case 'startplayquestion':
						$theattempt->{$adata->eventkey . 'time'}=$adata->timecreated;
						$theattempt->{$adata->eventkey . 'time_js'}=$adata->eventtime;
						$theattempt->qplaycount++;
						break;
					case 'endplayquestion':
					case 'revealanswers':
						$theattempt->{$adata->eventkey . 'time'}=$adata->timecreated;
						$theattempt->{$adata->eventkey . 'time_js'}=$adata->eventtime;
						break;
					case 'selectanswer':
						$theattempt->{$adata->eventkey . 'time'}=$adata->timecreated;
						$theattempt->{$adata->eventkey . 'time_js'}=$adata->eventtime;
						$theattempt->{$adata->eventkey}=$adata->eventvalue;
						break;
					default:
						$theattempt->{$adata->eventkey}=$adata->eventvalue;
						break;
			}//end of switch
		}//end of for each
		
		//stash the final parsed question
		if($theattempt){
			$attemptdata[]=$theattempt;
		}
		
		
		//At this point we have an event object per question from the log to process.
		//eg timetaken = $question->selectanswer - $question->endplayquestion;
		//need to make final and start "questions" have different ids (0 and 9999)
		
		//probably should loop here to get question duration data
		
		$this->rawdata= $attemptdata;
		return true;
	}

}


class mod_tquiz_allusers_report extends  mod_tquiz_base_report {
	
	protected $report="allusers";
	protected $fields = array('date','username','timetaken','score',);	
	protected $headingdata = null;
	protected $qcache=array();
	protected $ucache=array();
	
	public function fetch_formatted_field($field,$record,$withlinks){
				global $DB;
			switch($field){
				case 'date':
					$ret =  date("Y-m-d",$record->timecreated);
					break;
				case 'timetaken':
					$ret = $this->fetch_time_difference($record->timecreated,$record->timefinished);
					break;

				case 'username':
						$theuser = $this->fetch_cache('user',$record->userid);
						$ret = fullname($theuser);
					break;
				
				case 'score':
						$ret = $record->score;
					break;
				
				default:
					if(property_exists($record,$field)){
						$ret=$record->{$field};
					}else{
						$ret = '';
					}
			}
			return $ret;
	}
	
	public function fetch_formatted_heading(){
		return get_string('allusers','tquiz');
	}
	
	public function process_raw_data($formdata){
		global $DB;

		//no data in the heading, so an empty class even is overkill ..
		$this->headingdata = new stdClass();
		
		//the current attempts
		$alldata = $DB->get_records('tquiz_attempt',array('tquizid'=>$formdata->tquizid,'status'=>'current'));

		//At this point we have an event object per question from the log to process.
		//eg timetaken = $question->selectanswer - $question->endplayquestion;
		$this->rawdata= $alldata;
		return true;
	}

}