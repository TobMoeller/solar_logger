import socket
import json
import argparse

def send_command(ip, port, command, timeout):
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.settimeout(timeout)
            s.connect((ip, port))
            s.sendall(command.encode() + b'\n') # Inverter requires just LF as a terminator

            response = b''
            while True:
                part = s.recv(1024)
                response += part
                # Check if the end of the message has been reached (inverter uses CRLF for his response)
                if response.endswith(b'\r\n'):
                    break

            # Decode response to string, stripping the leading and trailing CRLF
            data = response.decode().strip('\r\n')
            return json.dumps({'success': True, 'data': data, 'error': None})

    # Errors are considered successful as the inverter shuts down and is unreachable when no power is produced
    except Exception as ex:
        return json.dumps({'success': True, 'data': None, 'error': str(ex)})


parser = argparse.ArgumentParser(description='Send a command to an inverter.')
parser.add_argument('ip_address', type=str, help='IP address')
parser.add_argument('port', type=int, help='Port number')
parser.add_argument('command', type=str, help='Command')
parser.add_argument('--timeout', type=int, default=5, help='Timeout in Seconds (default: 5)')

args = parser.parse_args()

response = send_command(args.ip_address, args.port, args.command, args.timeout)
# @TODO remove response
# response = json.dumps({'success': True, 'data': '123', 'error': None})

print(response)

