    function copy2clipboard(text)  
    {  
        if(window.clipboardData)  
        {  
            window.clipboardData.setData('text',text);  
            return true;  
        }  
        else  
        {  
            try {  
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");  
            } catch (e) {  
                alert("Internet Security settings do not allow copying to clipboard!");  
                return false;  
            }  
            try {  
                e = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);  
            } catch (e) {  
                return false;  
            }  
            try {  
                b = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);  
            } catch (e) {  
                return false;  
            }  
            b.addDataFlavor("text/unicode");  
            o = Components.classes['@mozilla.org/supports-string;1'].createInstance(Components.interfaces.nsISupportsString);  
            o.data = text;  
            b.setTransferData("text/unicode", o, text.length * 2);  
            try {  
                t = Components.interfaces.nsIClipboard;  
            } catch (e) {  
                return false;  
            }  
            e.setData(b, null, t.kGlobalClipboard);  
            return true;  
        }  
        alert('Copy doesn\'t work!');  
        return false;  
    }  
