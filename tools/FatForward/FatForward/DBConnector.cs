using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using MySql.Data.MySqlClient;
using System.Net;
using System.Threading;

namespace FatForward
{
    static class DBConnector
    {
        static public void getLobbys()
        {
            string l_Query = "SELECT `ip`, `port`, `status`, `online`, `max` FROM servers WHERE UNIX_TIMESTAMP() -UNIX_TIMESTAMP(laston) < 5 AND `max` > `online` AND `type` = 'lobby' AND `status` = 'open' ORDER BY `online` DESC";
            MySqlConnection l_Connection;
            string l_Server = "";
            string l_Database = "";
            string l_Uid = "";
            string l_Password = "";

            l_Server    = Environment.GetEnvironmentVariable("MYSQL_HOST");
            l_Database  = Environment.GetEnvironmentVariable("MYSQL_DATA");
            l_Uid       = Environment.GetEnvironmentVariable("MYSQL_USER");
            l_Password  = Environment.GetEnvironmentVariable("MYSQL_PASS");

            string connectionString = "SERVER=" + l_Server + ";" + "DATABASE=" +
            l_Database + ";" + "UID=" + l_Uid + ";" + "PASSWORD=" + l_Password + ";";

            l_Connection = new MySqlConnection(connectionString);

            while (true)
            {
                try
                {
                    l_Connection.Open();
                    break;
                }
                catch (Exception e)
                {
                    Console.WriteLine("DB not accessible");
                    Console.WriteLine("### Credentials ###");
                    Console.WriteLine("# MYSQL_HOST={0}", l_Server);
                    Console.WriteLine("# MYSQL_USER={0}", l_Uid);
                    Console.WriteLine("# MYSQL_DATA={0}", l_Database);
                    Console.WriteLine("###################\n");

                    Thread.Sleep(1000);
                    continue;
                }
            }

            Console.WriteLine("Sql connection Opened.");

            while (true)
            {
                //Create Command
                MySqlCommand cmd = new MySqlCommand(l_Query, l_Connection);
                //Create a data reader and Execute the command
               MySqlDataReader dataReader = cmd.ExecuteReader();

                int l_debug = 0;
                while (dataReader.Read())
                {
                    ServerStatus l_SerververStatus = new ServerStatus(dataReader["ip"].ToString(), int.Parse(dataReader["port"].ToString()), dataReader["status"].ToString(), int.Parse(dataReader["online"].ToString()), int.Parse(dataReader["max"].ToString()));

                    if (l_SerververStatus.m_Status != "open")
                        continue;

                    GlobalVars.g_ServerStatus.AddOrUpdate(l_SerververStatus.m_Ip + ":" + l_SerververStatus.m_Port, l_SerververStatus, (key, oldValue) => l_SerververStatus);
                    l_debug++;
                }
                dataReader.Close();
                Thread.Sleep(3000);
            }
        }
    }
}
