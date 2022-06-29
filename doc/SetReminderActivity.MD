# SetReminder Activity

Key: `set_reminder`
Type: automatic, no user interaction required

Example - set reminder for user WikiSysop on Main_Page for June 1st, 2022:

	<bpmn:task id="Activity_18egh9o" name="Add a reminder">
    	<bpmn:extensionElements>
			<wf:type>set_reminder</wf:type>
		</bpmn:extensionElements>
		<bpmn:property name="page" default="Main_Page"/>
		<bpmn:property name="username" default="WikiSysop"/>
		<bpmn:property name="date" default="2022-06-01"/>
		<bpmn:property name="comment" default="Just a reminder"/>
		<bpmn:property name="reminderId"/>
      <bpmn:incoming>Flow_1qh6vpl</bpmn:incoming>
      <bpmn:outgoing>Flow_0if1kyp</bpmn:outgoing>

    </bpmn:task>


## Description
This activity sets a reminder for a specific user and page.
Date for the reminder should be in format Y-m-d
