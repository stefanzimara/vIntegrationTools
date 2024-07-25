// Datum: 10.03.2022 - Author: Stefan Zimara, Valcoba AG 
// Version 1.0 
// Funktion: Log Message with SAP ID 

// -----------------------------------------------------------
import com.sap.gateway.ip.core.customdev.util.*;
import java.util.HashMap;
import static java.util.Calendar.*;
import org.w3c.dom.Node;
import groovy.xml.*;

import java.util.Map
import java.util.Iterator
import javax.activation.DataHandler


// -----------------------------------------------------------
def Message processData(Message message) {
    log("Default", message, true, true, true, true);
    return message
}

def Message logHeaderandProperties(Message message) {
    log("Header_and_Properties", message, false, true, true, true);
    return message
}

def Message payload_logger_after_mapping(Message message) {
    log("After_Mapping", message, true, false, false, false);
    return message
}

def Message payload_logger_before_mapping(Message message) {
    log("Before_Mapping", message, true, false, false, false);
    return message
}

def Message payload_source(Message message) {
    log("Source", message, true, true, true, true);
    return message
}

def Message payload_redirect(Message message) {
    log("Redirect Message", message, true, true, true, true);
    return message
}

def Message payload_response(Message message) {
    log("Response", message, true, true, true, true);
    return message
}

def Message payload_sent(Message message) {
    log("Sent Message", message, true, false, false, false);
    return message
}

def Message log_Info(Message message) {
    log("Info", message, true, false, false, false);
    return message
}

def Message logExceptionMessage(Message message) {
   log("Exception:", message, true, false, false, false);
    return message
}

// -----------------------------------------------------------

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

/*
log payload depending on property.ENABLE_LOGGING
*/
def Message payload_logger(Message message) {
    
    def headers = message.getHeaders();
    map = message.getProperties();
    property_ENABLE_LOGGING = map.get("EnableLogging");
    property_prefix = map.get("log.prefix");
    int logNumber = headers.get("logNumber", "1") as Integer;

    if (property_prefix == null || property_prefix.trim() == "") {
        property_prefix = "Payload"
    }    
    
    property_prefix = String.format("%04d", logNumber) + "." + property_prefix


    def validValues = ["TRUE", "ALL", "INFO", "DEBUG", "ERROR"]

    if (property_ENABLE_LOGGING != null && !property_ENABLE_LOGGING.trim().isEmpty() && validValues.contains(property_ENABLE_LOGGING.toUpperCase())) {
        Boolean messageLogged = create_attachment(message, property_prefix)
    }

    logNumber++;
    message.setHeader("logNumber", logNumber);
        
    return message;
}

def Message payload_logger_bm(Message message) {
    
    map = message.getProperties();
    property_ENABLE_LOGGING = map.get("EnableLogging");
    property_prefix = "Before_Mapping";

    def validValues = ["TRUE", "ALL", "INFO", "DEBUG", "ERROR"]

    if (property_ENABLE_LOGGING != null && !property_ENABLE_LOGGING.trim().isEmpty() && validValues.contains(property_ENABLE_LOGGING.toUpperCase())) {
        Boolean messageLogged = create_attachment(message, property_prefix)
    }

    return message;
}


def Message payload_logger_am(Message message) {
    
    map = message.getProperties();
    property_ENABLE_LOGGING = map.get("EnableLogging");
    property_prefix = "After_Mapping";

    def validValues = ["TRUE", "ALL", "INFO", "DEBUG", "ERROR"]

    if (property_ENABLE_LOGGING != null && !property_ENABLE_LOGGING.trim().isEmpty() && validValues.contains(property_ENABLE_LOGGING.toUpperCase())) {
        Boolean messageLogged = create_attachment(message, property_prefix)
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

def Message payload_logger_debug(Message message) {
    
    map = message.getProperties();
    property_ENABLE_LOGGING = map.get("EnableLogging");
    //message.setHeader("SAP_IsIgnoreProperties", new Boolean(true));

    def validValues = ["TRUE", "ALL", "DEBUG"]

    if (validValues.contains(property_ENABLE_LOGGING.toUpperCase())) {
        Boolean messageLogged = create_attachment(message, "Payload")
    }

    return message;
}

def Message payload_logger_error(Message message) {
    
    map = message.getProperties();
    property_ENABLE_LOGGING = map.get("EnableLogging");
    //message.setHeader("SAP_IsIgnoreProperties", new Boolean(true));

    def validValues = ["TRUE", "ALL", "ERROR", "DEBUG"]

    if (validValues.contains(property_ENABLE_LOGGING.toUpperCase())) {
        Boolean messageLogged = create_attachment(message, "Payload")
    }

    return message;
}

def Message payload_logger_Info(Message message) {
    
    map = message.getProperties();
    property_ENABLE_LOGGING = map.get("EnableLogging");
    //message.setHeader("SAP_IsIgnoreProperties", new Boolean(true));

    def validValues = ["TRUE", "ALL", "INFO"]

    if (validValues.contains(property_ENABLE_LOGGING.toUpperCase())) {
        Boolean messageLogged = create_attachment(message, "Payload")
    }

    return message;
}

/*
log payload depending on property.ENABLE_LOGGING
*/
def Message payload_logger_v2(Message message) {
    map = message.getProperties();
    property_ENABLE_LOGGING = map.get("EnableLogging");
    message.setHeader("SAP_IsIgnoreProperties", new Boolean(true));

    if (property_ENABLE_LOGGING.toUpperCase().equals("TRUE")) {
        def currentStep = message.getHeaders().get("CamelCxfMessage").getProcessingStep();
        def activityName = currentStep.getName();
        def position = currentStep.getProcessingStepPosition();

        def body = message.getBody(java.lang.String) as String;
        def messageLogFactory = messageLogFactory;
        def messageLog = messageLogFactory.getMessageLog(message);

        String contentType = message.getHeaders().get("Content-Type");

        messageLog.addAttachmentAsString("Payload_" + activityName + "_" + position, body, contentType);
    }

    return message;
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

//-----------------------------------------------------------
// Log Sub Routines
//-----------------------------------------------------------
def Message log(String prefix, Message message,boolean logPayload, boolean logHeaders, boolean logProperties, boolean logSysEnv) {

    def headers = message.getHeaders();
    def properties = properties.getProperties();
    boolean property_ENABLE_LOGGING = properties.get("EnableLogging", "TRUE") as boolean;

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

        //Prepare content, Header Log
        if(logHeaders) {
            logInfo.append(logMessageHeaders(message));
        } 

        //Prepare contentm Property Log
        if(logProperties) {
            logInfo.append(logMessageProperties(message));
        } 

        //Prepare contentm Property Log
        if(logSysEnv) {
            logInfo.append(logSystemEnvironment(message));
        } 

        def body = message.getBody(java.lang.String) as String;
        def messageLog = messageLogFactory.getMessageLog(message);

        if(logPayload) {
            if (logHeaders || logProperties || logSysEnv) {
                logInfo.append("Payload" + "\n--------------------------\n")
            }    
            logInfo.append(body);
        }
        
        logNumber++;
        message.setHeader("log.Number", logNumber);

        //String contentType = message.getHeaders().get("Content-Type");
        String contentType = determineContentType(logInfo.toString());
        messageLog.addAttachmentAsString(attachmentName, logInfo.toString(), contentType);

    }

    return message;
}



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


