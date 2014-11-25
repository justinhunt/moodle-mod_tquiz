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
 * JavaScript library for the tquiz module.
 *
 * @package    mod
 * @subpackage tquiz
 * @copyright  2014 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


M.mod_tquiz = M.mod_tquiz || {};

M.mod_tquiz.helper = {

    // YUI object.
    Y: null,
	
    //cleared to false on init
    opts: null,
	
	currentq: 0,
	
	evts: {},
	
	loadtime: 0,


    /**
     * @param Y the YUI object
     * @param start, the timer starting time, in seconds.
     * @param preview, is this a quiz preview?
     */
    init: function(Y,opts) {
    	// console.log('tquiz:start:' + start +':countdown:' + showcountdown + ':showcompletion:' + showcompletion);
        M.mod_tquiz.helper.Y = Y;
        M.mod_tquiz.helper.opts = opts;
		M.mod_tquiz.helper.loadtime = new Date().getTime();
    },
    
    // Define a function to handle the AJAX response.
    doresult: function(id,o,args) {
    	var id = id; // Transaction ID.
        var returndata = o.responseText; // Response data.
        var Y = M.mod_tquiz.helper.Y;
    	//console.log(returndata);
        var result = Y.JSON.parse(returndata);
        if(result.success){
        	console.log(result);
        }
    },
    
	answerclick: function(questionid,$answerid){
    	this.logevent(questionid, 'selectanswer',$answerid);
		this.logevent(questionid, 'selectanswertime',new Date().getTime());
		this.shownext();
	},
	
    logevent: function(questionid, eventkey, eventvalue){
    	var Y = this.Y;
		var opts = this.opts;
		var evts = this.opts;
		//don't log same event twice
		/*
		if(evts[$questionid + '@' + eventkey]){
			return;
		}
		*/
		evts[questionid + '@' + eventkey] = eventvalue;
    	var uri  = 'ajaxfriend.php?id=' +  opts['cmid'] + 
				'&questionid=' +  questionid +
    			'&eventkey=' +  eventkey +
    			'&eventvalue=' + eventvalue +
    			'&sesskey=' + M.cfg.sesskey;
		Y.on('io:complete', M.mod_tquiz.helper.doresult, Y,null);
		Y.io(uri);
		return;
    },
	
	dotransition: function(fromdiv,todiv){
		if(fromdiv){
			fromdiv.setStyle('display', 'none');
		}
		if(todiv){
			todiv.setStyle('display', 'block');
		}
		
		
	},
	
	shownext: function(){
		var fromdiv=false;
		var todiv=false;
		
		//get our from div
		if(this.currentq >0){
			var fromdiv = Y.one('#' + 'tquiz_qdiv_' + this.currentq);
		}else{
			var fromdiv = Y.one('#' + 'tquiz_intro_div');
		}
		//et our to div
		if(this.opts['quids'].length >0){
			this.currentq = this.opts['quids'].shift();
			var todiv = Y.one('#' + 'tquiz_qdiv_' + this.currentq);
		}else{
			var todiv  = Y.one('#' + 'tquiz_feedback_div');
		}
		this.dotransition(fromdiv,todiv);
	}
	
	
	
}

// Code for updating the countdown timer that is used on timed quizzes.
M.mod_tquiz.sm2 = {
    // YUI object.
    Y: null,
    
    //cleared to false on init
   // sounds: Array(),

    /**
     * @param Y the YUI object
     * @param Any opts SM needs
     */
    init: function(Y, opts) {
		this.Y = Y;
    	soundManager.setup({
		  url: opts['swfurl'],
		  flashVersion: 9, // optional: shiny features (default = 8)
		  // preferFlash: true;
		  preferFlash: false,
		  onready: function() {
			console.log('soundmanager ready');
		
			M.mod_tquiz.sm2.Y.Array.each(m_mod_tquiz_sm2_sounds, function(sound) {  	 
				 soundManager.createSound({
				// console.log('soundmanager url:' + sound.url);
				  id: sound.id, // optional: provide your own unique id
				  url: sound.url,
				   autoPlay: false
				  // onload: function() { console.log('sound loaded!', this); }
				  // other options here..
				});
			},null);
		}	
    });
	},
	
	handleaudioclick: function(audioid){
		soundManager.play(audioid);
	}
};