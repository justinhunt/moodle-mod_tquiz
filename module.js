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
		//in the case where we are previewing a single question in the preview/edit mode
		//we need to set the current q. In non edit mode views this is set in shownext when divs transition.
		if(opts['editmode']){
			this.currentq = this.opts['quids'].shift();
		}
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
		/*
			var togglegroups = this.Y.all('.mod_tquiz_togglegroup');
			togglegroups.each(function(togglegroup){
					var Y =  M.mod_tquiz.helper.Y;
					var bgr = new Y.ButtonGroup({
					srcNode: togglegroup,
					type: 'radio'
					}).render();
					
				//*COMMENTED HERE **
				}
			);
			*/
			
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
			console.log("loggin question");
			this.logevent(qid, 'startplayquestion','1');
		}else{
			console.log("loggin answer");
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
		var sb = Y.one('#mod_tquiz_submitbutton_' + M.mod_tquiz.helper.currentq  + '_button');
		if(!sb.hasClass('yui3-button-disabled')){
			this.logevent(questionid, 'selectanswer',this.currenta);
			this.shownext();
		}
	},
	
	selectaudioanswer_click: function(questionid,answerid){
		console.log('select audio answer click');
		this.currenta = answerid;
		var togglebuttons = this.Y.all('.mod_tquiz_selectaudioanswer_button');
		togglebuttons.each(function(thebutton) { 
				//thebutton.removeClass('mod_tquiz_selectedbutton');
				thebutton.removeClass('yui3-button-selected');
			}
		)
		//highlight selected button
		var thebutton = Y.one('#mod_tquiz_audioanswer' + questionid +'_' + answerid + '_button');
		//thebutton.addClass('mod_tquiz_selectedbutton');
		thebutton.addClass('yui3-button-selected');
		
		//enable select button
		var sb = Y.one('#mod_tquiz_submitbutton_' + M.mod_tquiz.helper.currentq  + '_button');
		sb.removeClass('yui3-button-disabled');
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
		//we don't want to do this twice, mostly because of messing up the logs
		if (ansdiv.getStyle('display')!='none'){return;}
		
		var hidingdiv = Y.one('#' + 'mod_tquiz_hiddenanswers_container_' + this.currentq);
		hidingdiv.setStyle('display', 'none');
		ansdiv.setStyle('display', 'block').transition(
				{ opacity: 1,
				  duration: this.opts['a_trans_time']}
		);
		this.logevent(this.currentq, 'revealanswers','1');
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
			//we are moving from start, so start the quiz timer if applic.
			M.mod_tquiz.timer.starttimer();
		}
		//et our to div
		if(this.opts['quids'].length >0){
			this.currentq = this.opts['quids'].shift();
			var todiv = Y.one('#' + 'tquiz_qdiv_' + this.currentq);
			//update progress bar
			this.updateprogressbar();
		}else{
			var todiv  = Y.one('#' + 'tquiz_feedback_div');
			//update progress bar
			this.clearprogressbar();
			this.logevent(0, 'finishquiz', 0);
		}
		this.doquestiontransition(fromdiv,todiv);
		
		
				
		//we do this to re-init the answer flag.
		//which I hate having to have
		this.currenta = 0;
	},
	
	clearprogressbar: function(){
		var pb = Y.one('#mod_tquiz_progressbar');
		pb.set('innerHTML','');
	},
	
	updateprogressbar: function(){
		var qcount = this.opts['qcount'];
		var pbtext  = (qcount - this.opts['quids'].length) + '/' + qcount;
		var pb = Y.one('#mod_tquiz_progressbar');
		pb.set('innerHTML',pbtext);
	},
	
	jumptoend: function(){
		var fromdiv=false;
		var todiv=false;
		
		//get our from div
		if(this.currentq >0){
			var fromdiv = Y.one('#' + 'tquiz_qdiv_' + this.currentq);
		}else{
			var fromdiv = Y.one('#' + 'tquiz_intro_div');
		}
		//et our to div
		var todiv  = Y.one('#' + 'tquiz_feedback_div');
		this.doquestiontransition(fromdiv,todiv);
		
		//we probably dont need to do this. but just in case
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

// Code for updating the countdown timer that is used on timed quizzes.
M.mod_tquiz.timer = {
    // YUI object.
    Y: null,
    
    //cleared to false on init
    iscomplete: true,

    // Timestamp at which time runs out, according to the student's computer's clock.
    endtime: 0,
	
	timelimit: 0,

    // This records the id of the timeout that updates the clock periodically,
    // so we can cancel.
    timeoutid: null,

    /**
     * @param Y the YUI object
     * @param start, the timer starting time, in seconds.
     * @param preview, is this a quiz preview?
     */
    init: function(Y, opts) {
    	// console.log('tquiz:start:' + start +':countdown:' + showcountdown + ':showcompletion:' + showcompletion);
        M.mod_tquiz.timer.Y = Y;
       // M.mod_tquiz.timer.endtime = M.pageloadstarttime.getTime() + opts['timelimit']*1000;
        M.mod_tquiz.timer.showcountdown = opts['showcountdown'];
		M.mod_tquiz.timer.timelimit = opts['timelimit'];

		if(opts['showcountdown']){
			Y.one('#tquiz-timer').setStyle('display', 'block');
		}
		
		//M.mod_tquiz.timer.update();
		//console.log('tquiz:counting' + start + ":" + M.mod_tquiz.timer.cmid + ":" + $completed);

    },
	
	 /**
     * Stop the timer, if it is running.
     */
    starttimer: function() {
        if (M.mod_tquiz.timer.timelimit>0) {
			M.mod_tquiz.timer.endtime =  new Date().getTime() + (M.mod_tquiz.timer.timelimit*1000);
			M.mod_tquiz.timer.update();
        }
    },


    /**
     * Stop the timer, if it is running.
     */
    stop: function(e) {
        if (M.mod_tquiz.timer.timeoutid) {
            clearTimeout(M.mod_tquiz.timer.timeoutid);
        }
    },

    /**
     * Function to convert a number between 0 and 99 to a two-digit string.
     */
    two_digit: function(num) {
        if (num < 10) {
            return '0' + num;
        } else {
            return num;
        }
    },

    // Define a function to handle the AJAX response.
    complete: function(id,o,args) {
    	var id = id; // Transaction ID.
        var returndata = o.responseText; // Response data.
       //console.log(returndata);
        var Y = M.mod_tquiz.timer.Y;
        Y.one('#tquiz-timer').setStyle('display', 'none');
        var result = Y.JSON.parse(returndata);
		if(result.success){
        	M.mod_tquiz.timer.iscomplete = true;
        	M.mod_tquiz.jscomplete.iscomplete = true;
        	if(M.mod_tquiz.timer.showcompletion){
        		Y.one('#tquiz-completed').setStyle('display', 'block');
        	}
        }

    },

    // Function to update the clock with the current time left, and submit the quiz if necessary.
    update: function() {
        var Y = M.mod_tquiz.timer.Y;
        var secondsleft = Math.floor((M.mod_tquiz.timer.endtime - new Date().getTime())/1000);

        // If time has expired, set the hidden form field that says time has expired and submit
        if (secondsleft < 0) {
            M.mod_tquiz.timer.stop(null);
			M.mod_tquiz.helper.logevent(0, 'quiztimedout', 0);
			M.mod_tquiz.helper.logevent(0, 'finishquiz', 0);
			M.mod_tquiz.helper.jumptoend();
            return;
        }

        // If time has nearly expired, change the colour.
        if (secondsleft < 100 &&  M.mod_tquiz.timer.showcountdown) {
            Y.one('#tquiz-timer').removeClass('timeleft' + (secondsleft + 2))
                    .removeClass('timeleft' + (secondsleft + 1))
                    .addClass('timeleft' + secondsleft);
        }

        // Update the time display.
        var hours = Math.floor(secondsleft/3600);
        secondsleft -= hours*3600;
        var minutes = Math.floor(secondsleft/60);
        secondsleft -= minutes*60;
        var seconds = secondsleft;
        Y.one('#tquiz-time-left').setContent(hours + ':' +
                M.mod_tquiz.timer.two_digit(minutes) + ':' +
                M.mod_tquiz.timer.two_digit(seconds));
        

        // Arrange for this method to be called again soon.
        M.mod_tquiz.timer.timeoutid = setTimeout(M.mod_tquiz.timer.update, 100);
    }
};