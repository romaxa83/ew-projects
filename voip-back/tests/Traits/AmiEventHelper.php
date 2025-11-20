<?php

namespace Tests\Traits;

use App\PAMI\Message\Event;

trait AmiEventHelper
{
    public function createQueueMemberEvent(
        string $queue = '10000',
        string $name = '1001',
        string $location = 'Local/1001@queue_members',
        string $interface = 'Custom:1001',
        string $membership = 'static',
        string $penalty = '0',
        string $callsTaken = '0',
        string $lastCall = '0',
        string $lastPause = '0',
        string $loginTime = '1678957325',
        string $inCall = '0',
        string $paused = '0',
        string $status = '1',
        string $pausedReason = '',
        string $wrapuptime = '0',
    ): Event\QueueMemberEvent
    {
        return new Event\QueueMemberEvent(
            "Event: QueueMember\r\n
                        Queue: ".$queue."\r\n
                        Name: ".$name."\r\n
                        Location: ".$location."\r\n
                        StateInterface: ".$interface."\r\n
                        Membership: ".$membership."\r\n
                        Penalty: ".$penalty."\r\n
                        CallsTaken: ".$callsTaken."\r\n
                        LastCall: ".$lastCall."\r\n
                        LastPause: ".$lastPause."\r\n
                        LoginTime: ".$loginTime."\r\n
                        InCall: ".$inCall."\r\n
                        Status: ".$status."\r\n
                        Paused: ".$paused."\r\n
                        PausedReason: ".$pausedReason."\r\n
                        Wrapuptime: ".$wrapuptime."\r\n
                        ActionID: 1679902096.6766"
        );
    }

    public function createQueueMemberStatusEvent(
        string $privilege = 'agent,all',
        string $systemName = 'asterisk-docker01',
        string $queue = 'support',
        string $memberName = '390',
        string $interface = 'Local/390@queue_members',
        string $stateInterface = 'Custom:390',
        string $membership = 'realtime',
        string $penalty = '0',
        string $callsTaken = '0',
        string $lastCall = '0',
        string $lastPause = '0',
        string $loginTime = '1678957325',
        string $inCall = '1',
        string $status = '1',
        string $paused = '0',
    ): Event\QueueMemberStatusEvent
    {
        return new Event\QueueMemberStatusEvent(
            "Event: QueueMemberStatus\r\n
                        Privilege: ".$privilege."\r\n
                        SystemName: ".$systemName."\r\n
                        Queue: ".$queue."\r\n
                        MemberName: ".$memberName."\r\n
                        Interface: ".$interface."\r\n
                        StateInterface: ".$stateInterface."\r\n
                        Membership: ".$membership."\r\n
                        Penalty: ".$penalty."\r\n
                        CallsTaken: ".$callsTaken."\r\n
                        LastCall: ".$lastCall."\r\n
                        LastPause: ".$lastPause."\r\n
                        LoginTime: ".$loginTime."\r\n
                        InCall: ".$inCall."\r\n
                        Status: ".$status."\r\n
                        Paused: ".$paused."\r\n
                        ActionID: 1679902096.6766"
        );
    }

    public function createQueueEntryEvent(
        string $queue,
        string $chanel,
        string $uniqID,
        string $position = '2',
        string $wait = '10',
        string $CallerIDNum = 'test',
        string $CallerIDName = 'unknown',
        string $ConnectedLineNum = 'unknown',
        string $ConnectedLineName = 'unknown',
    ): Event\QueueEntryEvent
    {
        return new Event\QueueEntryEvent(
            "Event: QueueEntry\r\n
                        Queue: ".$queue."\r\n
                        Position: ".$position."\r\n
                        Channel: ".$chanel."\r\n
                        Uniqueid: ".$uniqID."\r\n
                        CallerIDNum: ".$CallerIDNum."\r\n
                        CallerIDName: ".$CallerIDName."\r\n
                        ConnectedLineNum: ".$ConnectedLineNum."\r\n
                        ConnectedLineName: ".$ConnectedLineName."\r\n
                        Wait: ".$wait."\r\n
                        Priority: 0\r\n
                        ActionID: 1679902096.6766"
        );
    }

    public function createVarSetEvent(string $variable, string $value, string $chanel): Event\VarSetEvent
    {
        return new Event\VarSetEvent(
            "Event: VarSet\r\n
                        Privilege: dialplan,all\r\n
                        SystemName: asterisk-docker01\r\n
                        Channel: ".$chanel."\r\n
                        ChannelState: 4\r\n
                        ChannelStateDesc: Ring\r\n
                        CallerIDNum: 370\r\n
                        CallerIDName: cubic3 rubic3\r\n
                        ConnectedLineNum: unknown\r\n
                        ConnectedLineName: unknown\r\n
                        Language: en\r\n
                        AccountCode: \r\n
                        Context: ch-in\r\n
                        Exten: 00\r\n
                        Priority: 9\r\n
                        Uniqueid: asterisk-docker01-1679900941.54227\r\n
                        Linkedid: asterisk-docker01-1679900941.54227\r\n
                        Variable: ".$variable."\r\n
                        Value: ".$value.""
        );
    }

    public function createDialStateEvent(
        string $privilege = 'agent,all',
        string $systemName = 'asterisk-docker01',
        string $channel = 'Local/310@queue_members-0000015d;2',
        string $channelState = '4',
        string $channelStateDesc = 'Ring',
        string $callerIDNum = '+107806863',
        string $callerIDName = 'Anders 5N',
        string $connectedLineNum = '310',
        string $connectedLineName = '<unknown>',
        string $language = 'en',
        string $accountCode = '',
        string $context = 'queue_members',
        string $exten = '310',
        string $priority = '9',
        string $uniqueid = 'asterisk-docker01-1681221127.83780',
        string $linkedid = 'asterisk-docker01-1681221104.83763',
        string $destChannel = 'PJSIP/kamailio-00004c1e',
        string $destChannelState = '5',
        string $destChannelStateDesc = 'Ringing',
        string $destCallerIDNum = '310',
        string $destCallerIDName = '<unknown>',
        string $destConnectedLineNum = '+107806863',
        string $destConnectedLineName = 'Anders 5N',
        string $destLanguage = 'en',
        string $destAccountCode = '',
        string $destContext = 'ch-in',
        string $destExten = '310',
        string $destPriority = '1',
        string $destUniqueid = 'asterisk-docker01-1681221127.83784',
        string $destLinkedid = 'asterisk-docker01-1681221104.83763',
        string $dialStatus = 'RINGING',
    ): Event\DialStateEvent
    {
        return new Event\DialStateEvent(
            "Event: DialState\r\n
                        Privilege: ".$privilege."\r\n
                        SystemName: ".$systemName."\r\n
                        Channel: ".$channel."\r\n
                        ChannelState: ".$channelState."\r\n
                        ChannelStateDesc: ".$channelStateDesc."\r\n
                        CallerIDNum: ".$callerIDNum."\r\n
                        CallerIDName: ".$callerIDName."\r\n
                        ConnectedLineNum: ".$connectedLineNum."\r\n
                        ConnectedLineName: ".$connectedLineName."\r\n
                        Language: ".$language."\r\n
                        AccountCode: ".$accountCode."\r\n
                        Context: ".$context."\r\n
                        Exten: ".$exten."\r\n
                        Priority: ".$priority."\r\n
                        Uniqueid: ".$uniqueid."\r\n
                        Linkedid: ".$linkedid."\r\n
                        DestChannel: ".$destChannel."\r\n
                        DestChannelState: ".$destChannelState."\r\n
                        DestChannelStateDesc: ".$destChannelStateDesc."\r\n
                        DestCallerIDNum: ".$destCallerIDNum."\r\n
                        DestCallerIDName: ".$destCallerIDName."\r\n
                        DestConnectedLineNum: ".$destConnectedLineNum."\r\n
                        DestConnectedLineName: ".$destConnectedLineName."\r\n
                        DestLanguage: ".$destLanguage."\r\n
                        DestAccountCode: ".$destAccountCode."\r\n
                        DestContext: ".$destContext."\r\n
                        DestExten: ".$destExten."\r\n
                        DestPriority: ".$destPriority."\r\n
                        DestUniqueid: ".$destUniqueid."\r\n
                        DestLinkedid: ".$destLinkedid."\r\n
                        DialStatus: ".$dialStatus."\r\n
                        ActionID: 1679902096.6766"
        );
    }

    public function createDialEndEvent(
        string $privilege = 'agent,all',
        string $systemName = 'asterisk-docker01',
        string $channel = 'Local/310@queue_members-0000015d;2',
        string $channelState = '4',
        string $channelStateDesc = 'Ring',
        string $callerIDNum = '+107806863',
        string $callerIDName = 'Anders 5N',
        string $connectedLineNum = '310',
        string $connectedLineName = '<unknown>',
        string $language = 'en',
        string $accountCode = '',
        string $context = 'queue_members',
        string $exten = '310',
        string $priority = '9',
        string $uniqueid = 'asterisk-docker01-1681221127.83780',
        string $linkedid = 'asterisk-docker01-1681221104.83763',
        string $destChannel = 'PJSIP/kamailio-00004c1e',
        string $destChannelState = '5',
        string $destChannelStateDesc = 'Ringing',
        string $destCallerIDNum = '310',
        string $destCallerIDName = '<unknown>',
        string $destConnectedLineNum = '+107806863',
        string $destConnectedLineName = 'Anders 5N',
        string $destLanguage = 'en',
        string $destAccountCode = '',
        string $destContext = 'ch-in',
        string $destExten = '310',
        string $destPriority = '1',
        string $destUniqueid = 'asterisk-docker01-1681221127.83784',
        string $destLinkedid = 'asterisk-docker01-1681221104.83763',
        string $dialStatus = 'NOANSWER',
    ): Event\DialEndEvent
    {
        return new Event\DialEndEvent(
            "Event: DialEnd\r\n
                        Privilege: ".$privilege."\r\n
                        SystemName: ".$systemName."\r\n
                        Channel: ".$channel."\r\n
                        ChannelState: ".$channelState."\r\n
                        ChannelStateDesc: ".$channelStateDesc."\r\n
                        CallerIDNum: ".$callerIDNum."\r\n
                        CallerIDName: ".$callerIDName."\r\n
                        ConnectedLineNum: ".$connectedLineNum."\r\n
                        ConnectedLineName: ".$connectedLineName."\r\n
                        Language: ".$language."\r\n
                        AccountCode: ".$accountCode."\r\n
                        Context: ".$context."\r\n
                        Exten: ".$exten."\r\n
                        Priority: ".$priority."\r\n
                        Uniqueid: ".$uniqueid."\r\n
                        Linkedid: ".$linkedid."\r\n
                        DestChannel: ".$destChannel."\r\n
                        DestChannelState: ".$destChannelState."\r\n
                        DestChannelStateDesc: ".$destChannelStateDesc."\r\n
                        DestCallerIDNum: ".$destCallerIDNum."\r\n
                        DestCallerIDName: ".$destCallerIDName."\r\n
                        DestConnectedLineNum: ".$destConnectedLineNum."\r\n
                        DestConnectedLineName: ".$destConnectedLineName."\r\n
                        DestLanguage: ".$destLanguage."\r\n
                        DestAccountCode: ".$destAccountCode."\r\n
                        DestContext: ".$destContext."\r\n
                        DestExten: ".$destExten."\r\n
                        DestPriority: ".$destPriority."\r\n
                        DestUniqueid: ".$destUniqueid."\r\n
                        DestLinkedid: ".$destLinkedid."\r\n
                        DialStatus: ".$dialStatus."\r\n
                        ActionID: 1679902096.6766"
        );
    }

    public function createHangupEvent(
        string $privilege = 'agent,all',
        string $systemName = 'asterisk-docker01',
        string $channel = 'Local/310@queue_members-0000015d;2',
        string $channelState = '4',
        string $channelStateDesc = 'Ring',
        string $callerIDNum = '+107806863',
        string $callerIDName = 'Anders 5N',
        string $connectedLineNum = '310',
        string $connectedLineName = '<unknown>',
        string $language = 'en',
        string $accountCode = '',
        string $context = 'queue_members',
        string $exten = '310',
        string $priority = '9',
        string $uniqueid = 'asterisk-docker01-1681221127.83780',
        string $linkedid = 'asterisk-docker01-1681221104.83763',
        string $cause = '603',
        string $causeTxt = 'Unknown',
    ): Event\HangupEvent
    {
        return new Event\HangupEvent(
            "Event: Hangup\r\n
                        Privilege: ".$privilege."\r\n
                        SystemName: ".$systemName."\r\n
                        Channel: ".$channel."\r\n
                        ChannelState: ".$channelState."\r\n
                        ChannelStateDesc: ".$channelStateDesc."\r\n
                        CallerIDNum: ".$callerIDNum."\r\n
                        CallerIDName: ".$callerIDName."\r\n
                        ConnectedLineNum: ".$connectedLineNum."\r\n
                        ConnectedLineName: ".$connectedLineName."\r\n
                        Language: ".$language."\r\n
                        AccountCode: ".$accountCode."\r\n
                        Context: ".$context."\r\n
                        Exten: ".$exten."\r\n
                        Priority: ".$priority."\r\n
                        Uniqueid: ".$uniqueid."\r\n
                        Linkedid: ".$linkedid."\r\n
                        Cause: ".$cause."\r\n
                        Cause-txt: ".$causeTxt."\r\n
                        ActionID: 1679902096.6766"
        );
    }
}

