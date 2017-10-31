using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Net.Sockets;
using System.Text;
using System.Threading;
using System.Threading.Tasks;
using System.Collections.Concurrent;

namespace FatForward
{
    class GlobalVars
    {
        public const int TIMEOUT = 5;
        public static ConcurrentDictionary<string, Client> g_ClientDict = new ConcurrentDictionary<string, Client>();
        public static DBConnector g_Database = new DBConnector();
    }

    class Program
    {
        IPEndPoint m_EndPoint = new IPEndPoint(IPAddress.Any, 19132);

        UdpClient m_ClientToProxyReceiver;
        
        static void Main(string[] args)
        {
            new Program();
        }

        public Program()
        {
            m_ClientToProxyReceiver = new UdpClient(m_EndPoint);
            while (true)
            {
                IPEndPoint m_ClientIpEndPoint = new IPEndPoint(IPAddress.Any, 19132);

                byte[] l_Data = m_ClientToProxyReceiver.Receive(ref m_ClientIpEndPoint);

                if (GlobalVars.g_ClientDict.TryGetValue(m_ClientIpEndPoint.ToString(), out Client l_ExistingClient))
                {
                    l_ExistingClient.m_DataToSend.Enqueue(l_Data);
                    continue;
                }
                else
                {
                    var l_Client = new Client(ref m_ClientToProxyReceiver, m_ClientIpEndPoint);
                    l_Client.m_DataToSend.Enqueue(l_Data);

                    GlobalVars.g_ClientDict[m_ClientIpEndPoint.ToString()] = l_Client;
                    Console.WriteLine("Receive new connection from {0}", m_ClientIpEndPoint.ToString());
                }
            }
        }
    }
}
