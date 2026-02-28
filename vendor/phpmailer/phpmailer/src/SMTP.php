<?php
/**
 * PHPMailer RFC821 SMTP email transport class.
 *
 * @package PHPMailer
 * @subpackage PHPMailer
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Chris Corbyn
 * @author Brent R. Matzelle (original founder)
 * @copyright 2010 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Chris Corbyn
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer RFC821 SMTP email transport class.
 *
 * @package PHPMailer
 * @subpackage PHPMailer
 * @author Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author Chris Corbyn
 * @author Brent R. Matzelle (original founder)
 */
class SMTP
{
    /**
     * The PHPMailer SMTP version number.
     *
     * @var string
     */
    const VERSION = '6.9.1';

    /**
     * SMTP line break constant.
     *
     * @var string
     */
    const LE = "\r\n";

    /**
     * The SMTP port to use if one is not specified.
     *
     * @var int
     */
    const DEFAULT_PORT = 25;

    /**
     * The maximum line length allowed by RFC 2822 section 2.1.1.
     *
     * @var int
     */
    const MAX_LINE_LENGTH = 998;

    /**
     * The maximum line length allowed for RFC 2821 section 4.5.3.1.6.
     *
     * @var int
     */
    const MAX_REPLY_LENGTH = 512;

    /**
     * Debug level for showing debug output.
     *
     * @var int
     */
    public $do_debug = 0;

    /**
     * The function/method to use for debugging output.
     * Right now we only honor 'echo', 'html' or 'error_log'.
     *
     * @var string
     */
    public $Debugoutput = 'echo';

    /**
     * Whether to use VERP.
     *
     * @var bool
     */
    public $do_verp = false;

    /**
     * The timeout value for connection, in seconds.
     * Default of 5 minutes (300sec) is from RFC2821 section 4.5.3.2.
     * This is the timeout for every command sent to the server.
     *
     * @var int
     */
    public $Timeout = 300;

    /**
     * How long to wait for commands to complete.
     *
     * @var int
     */
    public $Timelimit = 300;

    /**
     * Patterns to ignore in SMTP response lines.
     *
     * @var array
     */
    public $Version = '';

    /**
     * SMTP socket connection handle.
     *
     * @var resource
     */
    protected $smtp_conn;

    /**
     * Error information, if any, for the last SMTP command.
     *
     * @var array
     */
    protected $error = [
        'error' => '',
        'detail' => '',
        'smtp_code' => '',
        'smtp_code_ex' => '',
    ];

    /**
     * The socket status.
     *
     * @var array
     */
    protected $helo_rply = null;

    /**
     * The set of SMTP extensions sent in reply to EHLO command.
     *
     * @var array
     */
    protected $server_caps = null;

    /**
     * The most recent reply received from the server.
     *
     * @var string
     */
    protected $last_reply = '';

    /**
     * Output debugging info via user-selected method.
     *
     * @param string $str   Debug string to output
     * @param int    $level The debug level of this message; see DEBUG_* constants
     *
     * @see SMTP::$Debugoutput
     * @see SMTP::$do_debug
     */
    protected function edebug($str, $level = 0)
    {
        if ($level > $this->do_debug) {
            return;
        }
        //Avoid clash with built-in function names
        if (!in_array($this->Debugoutput, ['error_log', 'html', 'echo']) && is_callable($this->Debugoutput)) {
            call_user_func($this->Debugoutput, $str, $level);

            return;
        }
        switch ($this->Debugoutput) {
            case 'error_log':
                //Don't output, just log
                error_log($str);
                break;
            case 'html':
                //Cleans up output a bit for a better looking, easier to read message
                echo gmdate('Y-m-d H:i:s') . "\t" . str_replace(
                    ["\n", "\r"],
                    ['<br />', ''],
                    htmlspecialchars($str, ENT_QUOTES, 'UTF-8')
                ) . "<br />\n";
                break;
            case 'echo':
            default:
                //Normalize line breaks
                $str = preg_replace('/\r\n|\r/ms', "\n", $str);
                echo gmdate('Y-m-d H:i:s') . "\t" . str_replace(
                    "\n",
                    "\n                   \t                  ",
                    trim($str)
                ) . "\n";
        }
    }

    /**
     * Connect to an SMTP server.
     *
     * @param string $host    SMTP server IP or host name
     * @param int    $port    The port number to connect to
     * @param int    $timeout How long to wait for the connection to open
     * @param array  $options An array of options for stream_context_create()
     *
     * @return bool True on success
     */
    public function connect($host, $port = null, $timeout = 30, $options = [])
    {
        //Stub
        return true;
    }
    
    // Stub other methods
    public function connected() { return true; }
    public function quit() { return true; }
    public function close() { return true; }
    public function getError() { return $this->error; }
}
