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
	
	currenta: 0,
	
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
		this.init_toggles();
    },
	
	donothing: function(){
		console.log('doingnothing');
	},
	
	init_toggles: function(){
	/*
		// A toggle button with a state change listener
		var toggles = this.Y.all('.mod_tquiz_togglebutton');
		console.log('fetching toggle buttons');
		console.log('size:' + toggles.size());
		//this.Y.Array.each(toggles, function(toggle) {  	
		toggles.each(function(toggle) { 
			console.log('making a toggle button');
			var newtogglebutton = new M.mod_tquiz.helper.Y.ToggleButton({
				srcNode:toggle,
				type: 'checkbox',
				// 'after', because 'on' would trigger before the attribute update
				after: {
					'pressedChange': function () {
						var button = this,
							pressed = button.get('pressed'),
							newLabel = 'this ' + (pressed ? 'pressed' : 'depressed') + ' button :' + (pressed ? ')' : '(');

						button.set('label', newLabel);
					}
				}
			}).render();
		});
		*/
		    // A group of radio-like buttons
			var togglegroups = this.Y.all('.mod_tquiz_togglegroup');
			togglegroups.each(function(togglegroup){
					var Y =  M.mod_tquiz.helper.Y;
					var bgr = new Y.ButtonGroup({
					srcNode: togglegroup,
					type: 'radio'
					}).render();
					
					bgr.on('selectionChange', function(e){
						var Y =  M.mod_tquiz.helper.Y;
						var sb = Y.one('#mod_tquiz_submitbutton_' + M.mod_tquiz.helper.currentq  + '_button');
						sb.disabled=false;
						var bgrbuttons = bgr.getSelectedButtons();
						var thebutton = bgrbuttons.shift();
						 M.mod_tquiz.helper.currenta = thebutton.getAttribute('value');
			});
					
					
				}
			);
			/*
			var buttonGroupRadio = new Y.ButtonGroup({
				srcNode: '#radioContainer',
				type: 'radio'
			})

			buttonGroupRadio.render();

			buttonGroupRadio.on('selectionChange', function(e){
				mod_tquiz_@@ID@@_button
				Y.log('buttonGroup selection changed');
			});
			*/
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
    
	startquiz: function(){
    	this.logevent(0, 'startquiz','1');
		this.shownext();
	},
	
	text_answer_click: function(questionid,answerid){
    	this.logevent(questionid, 'selectanswer',answerid);
		this.shownext();
	},
	
	audio_question_click: function(audioid){
		M.mod_tquiz.sm2.handleaudioclick(audioid);
		//this.logevent(this.currentq, 'playquestion','1');
		//this.revealanswers();
	},
	
	audio_answer_click: function(audioid){
		M.mod_tquiz.sm2.handleaudioclick(audioid);
	},
	
	onsoundfinish: function(audioid){
		console.log('soundfinished');
		var qid=M.mod_tquiz.sm2.sounds[audioid].questionid;
		var aid=M.mod_tquiz.sm2.sounds[audioid].answerid;
		//if its a question sound, log and reveal que
		if(!aid){
		//if its the right q
			if(qid==this.currentq){
				this.revealanswers();
			}
			this.logevent(qid, 'endplayquestion','1');
		}else{
			this.logevent(qid, 'endplayanswer',aid);
		}
	},
	
	onsoundplay: function(audioid){
		var qid=M.mod_tquiz.sm2.sounds[audioid].questionid;
		var aid=M.mod_tquiz.sm2.sounds[audioid].answerid;
		//if its a question sound, log and reveal que
		if(!aid){
			this.logevent(qid, 'startplayquestion','1');
		}else{
			this.logevent(qid, 'startplayanswer',aid);
		}
		
	},
	
	submitbutton_click: function(questionid){
		//var bg = this.Y.one('#mod_tquiz_allanswers_container_' + questionid);
		/*
		var bg = new this.Y.ButtonGroup({src: '#mod_tquiz_allanswers_container_' + questionid});
		var bgvalues = bg.getSelectedValues();
		var answerid = bgvalues.shift();
		*/
		this.logevent(questionid, 'selectanswer',this.currenta);
		this.shownext();
	},
	
    logevent: function(questionid, eventkey, eventvalue){
    	var Y = this.Y;
		var opts = this.opts;
		
		//bail if we are in preview mode
		if(opts['preview']){return;}
		
		var evts = this.evts;
		var eventtime = new Date().getTime();
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
				'&eventtime=' + eventtime +
    			'&sesskey=' + M.cfg.sesskey;
		//we dhoul donly declare this callback once. but actually it blocks
		//Y.on('io:complete', M.mod_tquiz.helper.doresult, Y,null);
		Y.io(uri);
		return;
    },
	
	revealanswers: function(){
		var ansdiv = Y.one('#' + 'mod_tquiz_allanswers_container_' + this.currentq);
		var hidingdiv = Y.one('#' + 'mod_tquiz_hiddenanswers_container_' + this.currentq);
		hidingdiv.setStyle('display', 'none');
		ansdiv.setStyle('display', 'block').transition(
				{ opacity: 1,
				  duration: this.opts['a_trans_time']}
		);
	},
	
	doquestiontransition: function(fromdiv,todiv){
		if(fromdiv){
			fromdiv.setStyle('display', 'none');
		}
		if(todiv){
			todiv.setStyle('display', 'block').transition(
				{ opacity: 1,
				  duration: this.opts['q_trans_time']}
			);
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
		this.doquestiontransition(fromdiv,todiv);
		
		//we do this to re-init the answer flag.
		//which I hate having to have
		this.currenta = 0;
	}
	
	
	
}

// Code for updating the countdown timer that is used on timed quizzes.
M.mod_tquiz.sm2 = {
    // YUI object.
    Y: null,
	
	sounds: {},

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
			//console.log('soundmanager ready');
			if(!m_mod_tquiz_sm2_sounds){return;}
			M.mod_tquiz.sm2.Y.Array.each(m_mod_tquiz_sm2_sounds, function(sound) {  	
				//console.log('doing sound');
				//console.log('soundid:' + sound.id);
				//console.log('soundurl:' + sound.url);
				//use this to store info about question/answer etc per sound
				M.mod_tquiz.sm2.sounds[sound.id]=sound;
				
				 soundManager.createSound({
				// console.log('soundmanager url:' + sound.url);
				  id: sound.id, // optional: provide your own unique id
				  url: sound.url,
				   autoPlay: false,
				   onfinish:  function() {
					   M.mod_tquiz.helper.onsoundfinish(this.id);
					 },
				   onplay:  function() {
					   M.mod_tquiz.helper.onsoundplay(this.id);
					 }
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