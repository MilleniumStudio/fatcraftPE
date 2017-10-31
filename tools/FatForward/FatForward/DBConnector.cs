using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using MySql.Data.MySqlClient;
using System.Net;

namespace FatForward
{
    class DBConnector
    {
        private MySqlConnection connection;
        private string server;
        private string database;
        private string uid;
        private string password;

        //Constructor
        public DBConnector()
        {
            Initialize();
        }

        //Initialize values
        private void Initialize()
        {
            /*            server = Environment.GetEnvironmentVariable("SERVER_IP");
                        database = Environment.GetEnvironmentVariable("MYSQL_DATA");
                        uid = Environment.GetEnvironmentVariable("MYSQL_USER");
                        password = Environment.GetEnvironmentVariable("MYSQL_PASS");*/

            server = "192.168.1.69";
            database = "fatcraft_pe";
            uid = "fatcraftpe";
            password = "s54c5xcw4v56xc74g534cxb54g65b4gf654145bg";

            string connectionString = "SERVER=" + server + ";" + "DATABASE=" +
            database + ";" + "UID=" + uid + ";" + "PASSWORD=" + password + ";";

            connection = new MySqlConnection(connectionString);
            OpenConnection();
        }

        //open connection to database
        private bool OpenConnection()
        {
            try
            {
                connection.Open();
                return true;
            }
            catch (MySqlException e)
            {
                Console.WriteLine("SQL connection failed : {0}", e.ToString());
                return false;
            }
        }

        //Close connection
        private bool CloseConnection()
        {
            try
            {
                connection.Close();
                return true;
            }
            catch (MySqlException e)
            {
                Console.WriteLine("SQL close connection failed : {0}", e.ToString());
                return false;
            }
        }

        public IPEndPoint getBestLobby()
        {
            string l_Query = "SELECT ip, PORT FROM servers WHERE UNIX_TIMESTAMP() -UNIX_TIMESTAMP(laston) < 5 AND `max` > `online` AND `type` = 'lobby' AND `status` = 'open' ORDER BY `online` DESC LIMIT 1";
            IPEndPoint l_ToReturn = null;
            string l_Result = "";

            //Create Command
            MySqlCommand cmd = new MySqlCommand(l_Query, connection);
            //Create a data reader and Execute the command
            MySqlDataReader dataReader = cmd.ExecuteReader();

            while (dataReader.Read())
                l_ToReturn = new IPEndPoint(IPAddress.Parse(dataReader["ip"].ToString()), int.Parse(dataReader["port"].ToString()));
            dataReader.Close();
            return l_ToReturn;
        }
    }
}
