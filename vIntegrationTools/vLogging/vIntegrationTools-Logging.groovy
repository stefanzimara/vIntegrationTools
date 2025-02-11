// -----------------------------------------------------------
// Datum: 10.03.2022 - Author: Stefan Zimara, Valcoba AG 
// Version 1.0 
// Funktion: Log Message with SAP ID 
// -----------------------------------------------------------



// -----------------------------------------------------------
// Libraray Import
// -----------------------------------------------------------
import com.sap.gateway.ip.core.customdev.util.*;
import java.util.HashMap;
import static java.util.Calendar.*;
import org.w3c.dom.Node;
import groovy.xml.*;

import java.util.Map
import java.util.Iterator
import javax.activation.DataHandler

import org.osgi.framework.FrameworkUtil
import org.osgi.framework.ServiceReference
import org.apache.camel.CamelContext

import com.sap.gateway.ip.core.customdev.util.Message;

// -----------------------------------------------------------
// Call Methods
// -----------------------------------------------------------
def Message processData(Message message) {
    log("Default", message, true, true, true, true, false, "ALL");
    return message
}

def Message logHeaderandProperties(Message message) {
    log("Header_and_Properties", message, false, true, true, true,  false, "ALL");
    return message
}

def Message logCamelEnvironment(Message message) {
    log("Camel_Environment", message, false, false, false, false,  true, "DEBUG");
    return message
}

def Message payload_logger_after_mapping(Message message) {
    log("After_Mapping", message, true, false, false, false,  false, "DEBUG");
    return message
}

def Message payload_logger_before_mapping(Message message) {
    log("Before_Mapping", message, true, false, false, false,  false, "DEBUG");
    return message
}

def Message payload_source(Message message) {
    log("Source", message, true, true, true, true,  false, "ALL");
    return message
}

def Message payload_redirect(Message message) {
    log("Redirect Message", message, true, true, true, true,  false, "ERROR");
    return message
}

def Message payload_response(Message message) {
    log("Response", message, true, true, true, true,  false, "INFO");
    return message
}

def Message payload_sent(Message message) {
    log("Sent Message", message, true, false, false, false,  false, "INFO");
    return message
}

def Message log_Info(Message message) {
    log("Info", message, true, false, false, false,  false, "INFO");
    return message
}

def Message logExceptionMessage(Message message) {
   log("Exception", message, true, false, false, false,  false, "ALL");
    return message
}

// -----------------------------------------------------------
// Main
// -----------------------------------------------------------
def Message log(String prefix, Message message,boolean logPayload, boolean logHeaders, boolean logProperties, boolean logSysEnv, boolean logCamelEnv, String logLevel = "") {

    // Define Log Lecels
    def levelMap = [:]

    levelMap["ALL"] = 0
    levelMap["TRACE"] = 0
    levelMap["TRUE"] = 0

    levelMap["DEBUG"] = 1
    levelMap["INFO"] = 5
    levelMap["ERROR"] = 6

    levelMap["NONE"] = 7
    levelMap["FALSE"] = 7

    boolean property_ENABLE_LOGGING = false;

    //Read Prperties
    def headers = message.getHeaders();
    def properties = message.getProperties();
    
    def enableLoggingValue = properties.get("log.EnableLogging", "TRUE").toUpperCase();
    
    if (logLevel != "" && enableLoggingValue != "TRUE" && enableLoggingValue != "FALSE") {
        
        if (levelMap.containsKey(logLevel.toUpperCase())) {
        
            prefix += "_" + logLevel
        
            if(levelMap[enableLoggingValue] >= levelMap[logLevel.toUpperCase()]) {
                property_ENABLE_LOGGING = true;
            }
        
        } else {
            property_ENABLE_LOGGING = enableLoggingValue?.toString()?.equalsIgnoreCase("true");   
        }

    } else {    
        property_ENABLE_LOGGING = enableLoggingValue?.toString()?.equalsIgnoreCase("true");   
    }

    //Prepare Log Attachment
    def messageLog = messageLogFactory.getMessageLog(message);

    if (property_ENABLE_LOGGING) {
        
        message.setHeader("SAP_IsIgnoreProperties", new Boolean(true));
        StringBuffer logInfo = new StringBuffer()
        int logNumber = headers.get("log.Number", "1") as Integer;

        if (prefix == "") {
            property_prefix = properties.get("log.prefix");
           
            if (property_prefix == null || property_prefix.trim() == "") {
                property_prefix = "Payload"
            }    
        } else {
            property_prefix = prefix
        }    
        
        property_prefix = String.format("%04d", logNumber) + "." + property_prefix
    
        // Define Attachment Name
        def attachmentName = determineAttachmentName(property_prefix, "");

        //Note LogeLevel
        def flowLogLevel = message.getProperty("SAP_MPL_LogLevel_Overall");
    
        //Prepare content, Header Log
        if(logHeaders) {
            logInfo.append(logMessageHeaders(message));
        } 

        //Prepare content Property Log
        if(logProperties) {
            logInfo.append(logMessageProperties(message));
        } 

        //Prepare content System / Tenant environment
        if(logSysEnv) {
            logInfo.append(logSystemEnvironment(message));
        } 
        
       //Prepare Camel environment
        if(logCamelEnv) {
            logInfo.append(logMessageCamel(message));
        }         
        
        def body = message.getBody(java.lang.String) as String;

        if(logPayload) {
            if (logHeaders || logProperties || logSysEnv) {
                logInfo.append("Payload" + "\n--------------------------\n")
            }    
            logInfo.append(body);
        }
        
        logNumber++;
        message.setHeader("log.Number", logNumber);

        String contentType = determineContentType(logInfo.toString());
        messageLog.addAttachmentAsString(attachmentName, logInfo.toString(), contentType);

    }

    return message;
}


def Message set_local_time(Message message) {    
    def localTimeZone = TimeZone.getTimeZone('Europe/Berlin');
    def cal = Calendar.instance;
    def date = cal.time;
    def dateFormat = 'yyyyMMdd\'-\'HHmmss';
    //Header
    message.setProperty("localTime", date.format(dateFormat, localTimeZone));     
    return message;
}

def Message exception_payload_log(Message message) {
	map = message.getProperties();
	message.setHeader("SAP_IsIgnoreProperties",new Boolean(true));

    def body = message.getBody(java.lang.String) as String;
    def messageLog = messageLogFactory.getMessageLog(message);
    messageLog.addAttachmentAsString("Exception:", body, "application/xml");

    return message;
}

/*
used for catching SOAP Response Error messages
*/
def Message fetch_response_error_message_soap(Message message) {
    def map = message.getProperties();
    def ex = map.get("CamelExceptionCaught");
    if (ex.getClass().getCanonicalName().equals("org.apache.cxf.binding.soap.SoapFault")) {
        // log, use, or set to body

        // You can also get statusCode or Message
        // String exceptionMessage = ex.getMessage();

        // Fault Detail Element
        def xml = XmlUtil.serialize(ex.getOrCreateDetail());
        message.setBody(xml);
    }
    
    return message;
}




def Message logException(Message message) {
       //Properties 
       def map =  message.getProperties();
       java.lang.String logger = map.get("logFile");
       if (logger == null){
           logger = new String();
       }
       logger += "EXCEPTION occured in processing Import " + System.lineSeparator()  ;
       
       // Store the logfile as property on the message object
       message.setProperty("logFile",logger);

       // Call methode to store Log
       storeLog(message);
}






//-----------------------------------------------------------
// Log Sub Routines
//-----------------------------------------------------------
def String logMessageHeaders(Message message) {
    
    def headers = message.getHeaders();
    StringBuffer headerInfo = new StringBuffer()

    headerInfo.append("Header" + "\n--------------------------\n")
    
    // Protokolliere die Header
    for (header in headers) {
        headerInfo.append("header." + header.getKey().toString() + ": " + header.getValue().toString() + "\n")
    }

    headerInfo.append("\n")
    
    return headerInfo.toString()
    
}

def String logMessageProperties(Message message) {
    
    def properties = message.getProperties();
    StringBuffer propertiesInfo = new StringBuffer()

    propertiesInfo.append("Properties" + "\n--------------------------\n")
            
    // Protokolliere die Properties
    for (property in properties) {
        propertiesInfo.append("property." + property.getKey().toString() + ": " + property.getValue().toString() + "\n")
    }

    propertiesInfo.append("\n")
    
    return propertiesInfo.toString()
    
}

def String logMessageCamel(Message message) {
    
    def headers = message.getHeaders();
    StringBuffer camelInfo = new StringBuffer()

    def bundleCtx = FrameworkUtil.getBundle(Class.forName("com.sap.gateway.ip.core.customdev.util.Message")).getBundleContext();
    ServiceReference[] srs = bundleCtx.getServiceReferences(CamelContext.class.getName(), null);

    if (srs && srs.length > 0) {
        
        // Den ersten verfügbaren CamelContext abrufen
        CamelContext camelContext = (CamelContext) bundleCtx.getService(srs[0])

        if (camelContext) {

            camelInfo.append("CamelContext Details:\n")
            camelInfo.append("----------------------------\n")
        
            // Attributes of CamelContext
            camelInfo.append("camelContext.getName: " + camelContext.getName() + "\n")
            camelInfo.append("camelContext.getVersion: " + camelContext.getVersion() + "\n")
            camelInfo.append("camelContext.getStatus: " + camelContext.getStatus() + "\n")
            camelInfo.append("camelContext.getUptim: " + camelContext.getUptime() + "\n")
            
            // Further important Parameter
            camelInfo.append("camelContext.getRegistry: " + camelContext.getRegistry() + "\n")
            camelInfo.append("Routes: " + camelContext.getRoutes().size() + "\n") 

        }    
    }    

    camelInfo.append("\n")
    
    return camelInfo.toString()
    
}

def String logSystemEnvironment(Message message) {
    
    def sysenv = System.getenv()
    StringBuffer systemInfo = new StringBuffer()

    systemInfo.append("System Environment" + "\n--------------------------\n")
            
    // Protokolliere die Properties
    for (sys in sysenv) {
        systemInfo.append("sysenv." + sys.getKey().toString() + ": " + sys.getValue().toString() + "\n")
    }

    systemInfo.append("\n")
    
    return systemInfo.toString()
    
}


def Boolean create_attachment(Message message, String prefix = "Payload", String subfix = "") {
    
    def body = message.getBody(java.lang.String) as String;
    def messageLog = messageLogFactory.getMessageLog(message);

    // Define Attachment Name
    def attachmentName = determineAttachmentName(property_prefix, "");

    // Bestimmen des Content-Type anhand des Payload-Inhalts
    String contentType = determineContentType(body);

    messageLog.addAttachmentAsString(attachmentName, body, contentType);

    return true
}

//-----------------------------------------------------------
// Figure Out Content Type
//-----------------------------------------------------------
def String determineContentType(String body) {
 
    if (body.trim().startsWith("<")) {
        contentType = "application/xml"; // Annahme, dass es sich um XML handelt
    } else if (body.trim().startsWith("{") || body.trim().startsWith("[")) {
        contentType = "application/json"; // Annahme, dass es sich um JSON handelt
    } else {
        contentType = "text/plain"; // Annahme, dass es sich um Text handelt
    }
    
    return contentType
    
}

def String determineAttachmentName(String prefix = "Payload", String subfix = "") {
 
    // Erfasse den aktuellen Zeitstempel
    def timestamp = new Date().format("yyyyMMddHHmmssSSS"); // Mit Millisekunden

    // Erweitere den ursprünglichen Namen des Attachments um den Zeitstempel
    def attachmentName = prefix + "_" + timestamp;

    if (subfix != "") {
        attachmentName = attachmentName + "_" + subfix
    }

    return attachmentName;

    
}

def Message increaseLogNumber(Message message) {
   
    properties = properties.getProperties();
    int logNumber = properties.get("logNumber", "1") as Integer;

    logNumber++;
    message.setProperty("logNumber", logNumber);

    return message
}

def Message select_attachment(Message message) {
    
   Map<String, DataHandler> attachments = message.getAttachments()
   if (attachments.isEmpty()) {
      throw new Exception ("No content in Attachment")
   } else {
      Iterator<DataHandler> it = attachments.values().iterator()
      DataHandler attachment = it.next()
      message.setBody(attachment.getContent())
   }
   
   return message
   
}
