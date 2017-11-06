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
        IPEndPoint m_PmmpEndPoint;

        /// Client stuffs

        UdpClient m_PocketMineTunnel;
        UdpClient m_MCPETunnel;
        IPEndPoint m_MCPEIpEndPoint;

        Int32 m_LastUpdateTime = -1;

        public ConcurrentQueue<byte[]> m_DataToSend = new ConcurrentQueue<byte[]>();

        Thread m_RecvThread = null;
        Thread m_SendThread = null;

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
            while (m_PmmpEndPoint == null)
            {
                m_PmmpEndPoint = getBestLobby();
                Thread.Sleep(500);
            }

            Console.WriteLine("\n###\nClient : {0}:{1} going to => {2}:{3}\n###\n", m_MCPEIpEndPoint.Address, m_MCPEIpEndPoint.Port, m_PmmpEndPoint.Address, m_PmmpEndPoint.Port);
            foreach (KeyValuePair<string, ServerStatus> l_Itterator in GlobalVars.g_ServerStatus)
            {
                ServerStatus l_Debug = l_Itterator.Value;
            }

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
                        GlobalVars.g_ClientDict.TryRemove(m_MCPEIpEndPoint.ToString(), out Client l_Client);
                        break;
                    }
                }
                catch (Exception e)
                {
                    try
                    {
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
                }
                catch (Exception e)
                {
                    //Console.WriteLine("{0}", e.ToString());
                    break;
                }
            }
        }

        public IPEndPoint getBestLobby()
        {
            IPEndPoint l_ToReturn = null;
            int l_BestPlayerAmmount = 0;

            Console.WriteLine("nb servers = {0}", GlobalVars.g_ServerStatus.Count,ToString());

            foreach (KeyValuePair<string, ServerStatus> l_Itterator in GlobalVars.g_ServerStatus)
            {
                ServerStatus l_ServerStatus = l_Itterator.Value;

                if (l_ServerStatus.m_Online < l_ServerStatus.m_Max && l_ServerStatus.m_Online >= l_BestPlayerAmmount)
                {
                    l_BestPlayerAmmount = l_ServerStatus.m_Online;
                    l_ToReturn = l_ServerStatus.m_EndPoint;
                }
                Console.WriteLine("l_ToReturn = {0} with {1}", l_ToReturn.ToString(), l_ServerStatus.m_Online);
            }

            return (l_ToReturn);
        }
    }
}
