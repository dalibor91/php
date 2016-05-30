/**
 * @author Dalibor Menkovic <dalibor.menkovic@gmail.com>
 * @type Object
 */
var Debug = {
    debug: {
        l: function(m) { this.log(m); },
        w: function(m) { this.warn(m); },
        i: function(m) { this.info(m); },
        log: function(m) { this.msg('info', m); }, 
        info: function(m) { this.msg('info', m); },
        warn: function(m) { this.msg('warn', m); }, 
        msg: function(type, msg) {
            if (Debug.isActive && typeof console != 'undefined' && typeof console[type] != 'undefined') {
                console[type](msg);
            }
        }
    },
    
    setVar: function(k) {
        window[k] = this.debug;
    },
    
    setActive: function(s) {
        if (typeof s == 'function') {
            this.isActive = s();
        } else {
            this.isActive = s;
        }
    }, 
    
    isActive: false
};

Debug.setActive(function() {
    return window.location.hash == '#dbg'; 
});

Debug.setVar('dbg');



