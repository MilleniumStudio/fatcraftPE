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
    class ServerStatus
    {
        public ServerStatus(string p_Ip, int p_Port, string p_Status, int p_Online, int p_Max)
        {
            m_Ip = p_Ip;
            m_Port = p_Port;
            m_Status = p_Status;
            m_Online = p_Online;
            m_Max = p_Max;
            m_EndPoint = new IPEndPoint(IPAddress.Parse(m_Ip), m_Port);
        }

        public string m_Ip;
        public int m_Port;
        public IPEndPoint m_EndPoint;
        public string m_Status;
        public int m_Online;
        public int m_Max;
    }

    class GlobalVars
    {
        public const int TIMEOUT = 5;
        public static ConcurrentDictionary<string, Client> g_ClientDict = new ConcurrentDictionary<string, Client>();
        public static ConcurrentDictionary<string, ServerStatus> g_ServerStatus = new ConcurrentDictionary<string, ServerStatus>();
    }

    class Program
    {
        IPEndPoint m_EndPoint = new IPEndPoint(IPAddress.Any, 19132);

        UdpClient m_ClientToProxyReceiver;

        Thread m_UpdateServerListThread;
        
        static void Main(string[] args)
        {
            new Program();
        }

        public Program()
        {
            m_ClientToProxyReceiver = new UdpClient(m_EndPoint);

            m_UpdateServerListThread = new Thread(DBConnector.getLobbys);
            m_UpdateServerListThread.Start();

            while (true)
            {
                if (GlobalVars.g_ServerStatus.IsEmpty)
                {
                    Thread.Sleep(500);
                    continue;
                }

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
                    //Console.WriteLine("Receive new connection from {0}", m_ClientIpEndPoint.ToString());
                }
            }
        }
    }
}
