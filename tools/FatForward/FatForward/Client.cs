using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

using System.Threading.Tasks;
using System.Threading;

using System.Net;
using System.Net.Sockets;
using System.Collections.Concurrent;

namespace FatForward
{
    class Client
    {
        /// Pmmp stuffs
        static string m_PmmpIp = "192.168.1.32";
        static int m_PmmpPort = 19132;
        IPEndPoint m_PmmpEndPoint;

        /// Client stuffs

        UdpClient m_PocketMineTunnel;
        UdpClient m_MCPETunnel;
        IPEndPoint m_MCPEIpEndPoint;

        Int32 m_LastUpdateTime = -1;

        public ConcurrentQueue<byte[]> m_DataToSend = new ConcurrentQueue<byte[]>();

        Thread m_RecvThread = null;
        Thread m_SendThread = null;

        bool m_ShouldTerminate = false;

        public Client(ref UdpClient p_MCPETunnel, IPEndPoint p_MCPEIpEndPoint)
        {
            m_LastUpdateTime = (Int32)(DateTime.UtcNow.Subtract(new DateTime(1970, 1, 1))).TotalSeconds;

            m_MCPETunnel = p_MCPETunnel;
            m_MCPEIpEndPoint = p_MCPEIpEndPoint;

            m_SendThread = new Thread(ProcessReceivedClientPackets);
            m_SendThread.Start();

        }

        /// <summary>
        ///  Send packet received from MCPE Client to PocketMine MP Server
        /// </summary>
        public void ProcessReceivedClientPackets()
        {
            m_PmmpEndPoint = GlobalVars.g_Database.getBestLobby();

            m_PocketMineTunnel = new UdpClient();
            m_PocketMineTunnel.Client.ReceiveTimeout = GlobalVars.TIMEOUT * 1000;
            m_PocketMineTunnel.Connect(m_PmmpEndPoint);

            m_RecvThread = new Thread(ProcessReceivedServerPackets);
            m_RecvThread.Start();

            while (true)
            {
                byte[] l_Data;
                try
                {
                    if (m_DataToSend.TryDequeue(out l_Data))
                    {
                        //Console.WriteLine("MCPE->PMMP :" + Encoding.UTF8.GetString(l_Data));
                        m_PocketMineTunnel.Send(l_Data, l_Data.Length);
                        m_LastUpdateTime = (Int32)(DateTime.UtcNow.Subtract(new DateTime(1970, 1, 1))).TotalSeconds;
                    }
                    if (m_LastUpdateTime + GlobalVars.TIMEOUT < (Int32)(DateTime.UtcNow.Subtract(new DateTime(1970, 1, 1))).TotalSeconds)
                    {
                        m_PocketMineTunnel.Close();
                        Console.WriteLine("Close : {0} ", m_MCPEIpEndPoint.ToString());
                        //Console.WriteLine("Client 1 : {0}", GlobalVars.g_ClientDict.Count);
                        GlobalVars.g_ClientDict.TryRemove(m_MCPEIpEndPoint.ToString(), out Client l_Client);
                        //Console.WriteLine("m_LastUpdateTime : {2} / now : {4}  / i am : {1} Clients remaining : {0}", GlobalVars.g_ClientDict.Count, m_MCPEIpEndPoint.ToString(), m_LastUpdateTime, m_LastUpdateTime, (Int32)(DateTime.UtcNow.Subtract(new DateTime(1970, 1, 1))).TotalSeconds);
                        break;
                    }
                }
                catch (Exception e)
                {
                    try
                    {
                        //Console.WriteLine("{0}", e.ToString());
                        GlobalVars.g_ClientDict.TryRemove(m_MCPEIpEndPoint.ToString(), out Client l_Client);
                        m_PocketMineTunnel.Close();
                        break;
                    }
                    catch (Exception subException)
                    {
                        //Console.WriteLine("Failed to close socket or fail to remove of dictionnary : {0}", subException.ToString());
                        break;
                    }
                }
                Thread.Sleep(1);
            }
        }

        /// <summary>
        /// Send packet received from PocketMine MP Server to MCPE Client
        /// </summary>
        public void ProcessReceivedServerPackets()
        {
            while (true)
            {
                try
                {
                    byte[] l_Data = m_PocketMineTunnel.Receive(ref m_PmmpEndPoint);
                    //Console.WriteLine("PMMP->MCPE :" + Encoding.UTF8.GetString(l_Data));
                    m_MCPETunnel.Send(l_Data, l_Data.Length, m_MCPEIpEndPoint);
                    m_LastUpdateTime = (Int32)(DateTime.UtcNow.Subtract(new DateTime(1970, 1, 1))).TotalSeconds;
                    //Console.WriteLine("i am : {1} Clients remaining : {0}", GlobalVars.g_ClientDict.Count, m_MCPEIpEndPoint.ToString());
                }
                catch (Exception e)
                {
                    //Console.WriteLine("{0}", e.ToString());
                    break;
                }
            }
        }
    }
}
