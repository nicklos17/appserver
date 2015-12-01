<?php
namespace thriftrpc;
/**
 * Autogenerated by Thrift Compiler (0.9.1)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;


interface QQBindThriftRpcIf {
  public function QQBindDevice($sn, $openId, $accessToken);
  public function QQBindDevices($snData, $openId, $accessToken);
  public function QQUnbindDevice($sn, $openId);
  public function QQUnbindDevices($snData, $openId);
  public function QQSendMessage($sn, $openId, $message);
  public function QQDeviceLocate($sn, $din, $openId);
}

class QQBindThriftRpcClient implements \thriftrpc\QQBindThriftRpcIf {
  protected $input_ = null;
  protected $output_ = null;

  protected $seqid_ = 0;

  public function __construct($input, $output=null) {
    $this->input_ = $input;
    $this->output_ = $output ? $output : $input;
  }

  public function QQBindDevice($sn, $openId, $accessToken)
  {
    $this->send_QQBindDevice($sn, $openId, $accessToken);
  }

  public function send_QQBindDevice($sn, $openId, $accessToken)
  {
    $args = new \thriftrpc\ThriftRpc_QQBindDevice_args();
    $args->sn = $sn;
    $args->openId = $openId;
    $args->accessToken = $accessToken;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'QQBindDevice', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('QQBindDevice', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }
  public function QQBindDevices($snData, $openId, $accessToken)
  {
    $this->send_QQBindDevices($snData, $openId, $accessToken);
  }

  public function send_QQBindDevices($snData, $openId, $accessToken)
  {
    $args = new \thriftrpc\ThriftRpc_QQBindDevices_args();
    $args->snData = $snData;
    $args->openId = $openId;
    $args->accessToken = $accessToken;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'QQBindDevices', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('QQBindDevices', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }
  public function QQUnbindDevice($sn, $openId)
  {
    $this->send_QQUnbindDevice($sn, $openId);
  }

  public function send_QQUnbindDevice($sn, $openId)
  {
    $args = new \thriftrpc\ThriftRpc_QQUnbindDevice_args();
    $args->sn = $sn;
    $args->openId = $openId;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'QQUnbindDevice', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('QQUnbindDevice', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }
  public function QQUnbindDevices($snData, $openId)
  {
    $this->send_QQUnbindDevices($snData, $openId);
  }

  public function send_QQUnbindDevices($snData, $openId)
  {
    $args = new \thriftrpc\ThriftRpc_QQUnbindDevices_args();
    $args->snData = $snData;
    $args->openId = $openId;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'QQUnbindDevices', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('QQUnbindDevices', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }
  public function QQSendMessage($sn, $openId, $message)
  {
    $this->send_QQSendMessage($sn, $openId, $message);
  }

  public function send_QQSendMessage($sn, $openId, $message)
  {
    $args = new \thriftrpc\ThriftRpc_QQSendMessage_args();
    $args->sn = $sn;
    $args->openId = $openId;
    $args->message = $message;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'QQSendMessage', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('QQSendMessage', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }
  public function QQDeviceLocate($sn, $din, $openId)
  {
    $this->send_QQDeviceLocate($sn, $din, $openId);
    return $this->recv_QQDeviceLocate();
  }

  public function send_QQDeviceLocate($sn, $din, $openId)
  {
    $args = new \thriftrpc\ThriftRpc_QQDeviceLocate_args();
    $args->sn = $sn;
    $args->din = $din;
    $args->openId = $openId;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'QQDeviceLocate', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('QQDeviceLocate', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_QQDeviceLocate()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\thriftrpc\ThriftRpc_QQDeviceLocate_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \thriftrpc\ThriftRpc_QQDeviceLocate_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("QQDeviceLocate failed: unknown result");
  }

}

// HELPER FUNCTIONS AND STRUCTURES

class ThriftRpc_QQBindDevice_args {
  static $_TSPEC;

  public $sn = null;
  public $openId = null;
  public $accessToken = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'sn',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'openId',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'accessToken',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['sn'])) {
        $this->sn = $vals['sn'];
      }
      if (isset($vals['openId'])) {
        $this->openId = $vals['openId'];
      }
      if (isset($vals['accessToken'])) {
        $this->accessToken = $vals['accessToken'];
      }
    }
  }

  public function getName() {
    return 'ThriftRpc_QQBindDevice_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->sn);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->openId);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->accessToken);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('ThriftRpc_QQBindDevice_args');
    if ($this->sn !== null) {
      $xfer += $output->writeFieldBegin('sn', TType::STRING, 1);
      $xfer += $output->writeString($this->sn);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->openId !== null) {
      $xfer += $output->writeFieldBegin('openId', TType::STRING, 2);
      $xfer += $output->writeString($this->openId);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->accessToken !== null) {
      $xfer += $output->writeFieldBegin('accessToken', TType::STRING, 3);
      $xfer += $output->writeString($this->accessToken);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class ThriftRpc_QQBindDevices_args {
  static $_TSPEC;

  public $snData = null;
  public $openId = null;
  public $accessToken = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'snData',
          'type' => TType::LST,
          'etype' => TType::STRING,
          'elem' => array(
            'type' => TType::STRING,
            ),
          ),
        2 => array(
          'var' => 'openId',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'accessToken',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['snData'])) {
        $this->snData = $vals['snData'];
      }
      if (isset($vals['openId'])) {
        $this->openId = $vals['openId'];
      }
      if (isset($vals['accessToken'])) {
        $this->accessToken = $vals['accessToken'];
      }
    }
  }

  public function getName() {
    return 'ThriftRpc_QQBindDevices_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::LST) {
            $this->snData = array();
            $_size0 = 0;
            $_etype3 = 0;
            $xfer += $input->readListBegin($_etype3, $_size0);
            for ($_i4 = 0; $_i4 < $_size0; ++$_i4)
            {
              $elem5 = null;
              $xfer += $input->readString($elem5);
              $this->snData []= $elem5;
            }
            $xfer += $input->readListEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->openId);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->accessToken);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('ThriftRpc_QQBindDevices_args');
    if ($this->snData !== null) {
      if (!is_array($this->snData)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('snData', TType::LST, 1);
      {
        $output->writeListBegin(TType::STRING, count($this->snData));
        {
          foreach ($this->snData as $iter6)
          {
            $xfer += $output->writeString($iter6);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->openId !== null) {
      $xfer += $output->writeFieldBegin('openId', TType::STRING, 2);
      $xfer += $output->writeString($this->openId);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->accessToken !== null) {
      $xfer += $output->writeFieldBegin('accessToken', TType::STRING, 3);
      $xfer += $output->writeString($this->accessToken);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class ThriftRpc_QQUnbindDevice_args {
  static $_TSPEC;

  public $sn = null;
  public $openId = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'sn',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'openId',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['sn'])) {
        $this->sn = $vals['sn'];
      }
      if (isset($vals['openId'])) {
        $this->openId = $vals['openId'];
      }
    }
  }

  public function getName() {
    return 'ThriftRpc_QQUnbindDevice_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->sn);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->openId);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('ThriftRpc_QQUnbindDevice_args');
    if ($this->sn !== null) {
      $xfer += $output->writeFieldBegin('sn', TType::STRING, 1);
      $xfer += $output->writeString($this->sn);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->openId !== null) {
      $xfer += $output->writeFieldBegin('openId', TType::STRING, 2);
      $xfer += $output->writeString($this->openId);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class ThriftRpc_QQUnbindDevices_args {
  static $_TSPEC;

  public $snData = null;
  public $openId = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'snData',
          'type' => TType::LST,
          'etype' => TType::STRING,
          'elem' => array(
            'type' => TType::STRING,
            ),
          ),
        2 => array(
          'var' => 'openId',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['snData'])) {
        $this->snData = $vals['snData'];
      }
      if (isset($vals['openId'])) {
        $this->openId = $vals['openId'];
      }
    }
  }

  public function getName() {
    return 'ThriftRpc_QQUnbindDevices_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::LST) {
            $this->snData = array();
            $_size7 = 0;
            $_etype10 = 0;
            $xfer += $input->readListBegin($_etype10, $_size7);
            for ($_i11 = 0; $_i11 < $_size7; ++$_i11)
            {
              $elem12 = null;
              $xfer += $input->readString($elem12);
              $this->snData []= $elem12;
            }
            $xfer += $input->readListEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->openId);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('ThriftRpc_QQUnbindDevices_args');
    if ($this->snData !== null) {
      if (!is_array($this->snData)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('snData', TType::LST, 1);
      {
        $output->writeListBegin(TType::STRING, count($this->snData));
        {
          foreach ($this->snData as $iter13)
          {
            $xfer += $output->writeString($iter13);
          }
        }
        $output->writeListEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    if ($this->openId !== null) {
      $xfer += $output->writeFieldBegin('openId', TType::STRING, 2);
      $xfer += $output->writeString($this->openId);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class ThriftRpc_QQSendMessage_args {
  static $_TSPEC;

  public $sn = null;
  public $openId = null;
  public $message = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'sn',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'openId',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'message',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['sn'])) {
        $this->sn = $vals['sn'];
      }
      if (isset($vals['openId'])) {
        $this->openId = $vals['openId'];
      }
      if (isset($vals['message'])) {
        $this->message = $vals['message'];
      }
    }
  }

  public function getName() {
    return 'ThriftRpc_QQSendMessage_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->sn);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->openId);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->message);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('ThriftRpc_QQSendMessage_args');
    if ($this->sn !== null) {
      $xfer += $output->writeFieldBegin('sn', TType::STRING, 1);
      $xfer += $output->writeString($this->sn);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->openId !== null) {
      $xfer += $output->writeFieldBegin('openId', TType::STRING, 2);
      $xfer += $output->writeString($this->openId);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->message !== null) {
      $xfer += $output->writeFieldBegin('message', TType::STRING, 3);
      $xfer += $output->writeString($this->message);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class ThriftRpc_QQDeviceLocate_args {
  static $_TSPEC;

  public $sn = null;
  public $din = null;
  public $openId = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'sn',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'din',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'openId',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['sn'])) {
        $this->sn = $vals['sn'];
      }
      if (isset($vals['din'])) {
        $this->din = $vals['din'];
      }
      if (isset($vals['openId'])) {
        $this->openId = $vals['openId'];
      }
    }
  }

  public function getName() {
    return 'ThriftRpc_QQDeviceLocate_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->sn);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 2:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->din);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        case 3:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->openId);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('ThriftRpc_QQDeviceLocate_args');
    if ($this->sn !== null) {
      $xfer += $output->writeFieldBegin('sn', TType::STRING, 1);
      $xfer += $output->writeString($this->sn);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->din !== null) {
      $xfer += $output->writeFieldBegin('din', TType::STRING, 2);
      $xfer += $output->writeString($this->din);
      $xfer += $output->writeFieldEnd();
    }
    if ($this->openId !== null) {
      $xfer += $output->writeFieldBegin('openId', TType::STRING, 3);
      $xfer += $output->writeString($this->openId);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class ThriftRpc_QQDeviceLocate_result {
  static $_TSPEC;

  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
    }
  }

  public function getName() {
    return 'ThriftRpc_QQDeviceLocate_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::STRING) {
            $xfer += $input->readString($this->success);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('ThriftRpc_QQDeviceLocate_result');
    if ($this->success !== null) {
      $xfer += $output->writeFieldBegin('success', TType::STRING, 0);
      $xfer += $output->writeString($this->success);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}


