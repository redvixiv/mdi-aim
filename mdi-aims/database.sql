-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2026 at 04:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mdi_aims`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounting_coa`
--

CREATE TABLE `accounting_coa` (
  `Account_ID` int(11) NOT NULL,
  `Account_Code` varchar(20) DEFAULT NULL,
  `Account_Name` varchar(100) DEFAULT NULL,
  `Account_Type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounting_coa`
--

INSERT INTO `accounting_coa` (`Account_ID`, `Account_Code`, `Account_Name`, `Account_Type`) VALUES
(1, '1010', 'Cash in Bank / On Hand', 'Asset'),
(2, '1200', 'Accounts Receivable', 'Asset'),
(3, '1300', 'Inventory Asset', 'Asset'),
(4, '2000', 'Accounts Payable', 'Liability'),
(5, '3000', 'Owner Equity', 'Equity'),
(6, '4000', 'Sales Revenue', 'Revenue'),
(7, '5000', 'Cost of Goods Sold', 'Expense'),
(8, '1205', 'Creditable Withholding Tax', 'Asset'),
(10, '1500', 'Accumulated Depreciation', 'Asset'),
(11, '5100', 'Depreciation Expense', 'Expense'),
(5289, '2100', 'Output VAT Payable', 'Liability'),
(8774, '5300', 'Salaries and Wages', 'Expense'),
(8775, '2201', 'SSS Payable', 'Liability'),
(8776, '2202', 'PhilHealth Payable', 'Liability'),
(8777, '2203', 'Pag-IBIG Payable', 'Liability'),
(8778, '2204', 'Withholding Tax Payable', 'Liability'),
(9607, '5400', 'Fuel and Oil Expense', 'Expense'),
(9608, '5401', 'Repairs and Maintenance', 'Expense'),
(9609, '5402', 'Taxes and Licenses', 'Expense');

-- --------------------------------------------------------

--
-- Table structure for table `accounting_expenses`
--

CREATE TABLE `accounting_expenses` (
  `Expense_ID` int(11) NOT NULL,
  `Expense_Date` date DEFAULT NULL,
  `Account_ID` int(11) DEFAULT NULL,
  `Amount` decimal(15,2) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Reference_No` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_journal`
--

CREATE TABLE `accounting_journal` (
  `Journal_ID` int(11) NOT NULL,
  `Journal_Date` date DEFAULT NULL,
  `Reference_No` varchar(100) DEFAULT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounting_journal`
--

INSERT INTO `accounting_journal` (`Journal_ID`, `Journal_Date`, `Reference_No`, `Description`) VALUES
(1, '2026-07-28', 'DR-DR 1234567', 'Inventory received from DR-DR 1234567');

-- --------------------------------------------------------

--
-- Table structure for table `accounting_journal_lines`
--

CREATE TABLE `accounting_journal_lines` (
  `Line_ID` int(11) NOT NULL,
  `Journal_ID` int(11) DEFAULT NULL,
  `Account_ID` int(11) DEFAULT NULL,
  `Debit` decimal(15,2) DEFAULT 0.00,
  `Credit` decimal(15,2) DEFAULT 0.00,
  `Cleared_Status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounting_journal_lines`
--

INSERT INTO `accounting_journal_lines` (`Line_ID`, `Journal_ID`, `Account_ID`, `Debit`, `Credit`, `Cleared_Status`) VALUES
(1, 1, 3, 1940000.00, 0.00, 'Pending'),
(2, 1, 4, 0.00, 1940000.00, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `accounting_payables`
--

CREATE TABLE `accounting_payables` (
  `AP_ID` int(11) NOT NULL,
  `Supplier_ID` int(11) DEFAULT NULL,
  `Reference_No` varchar(100) DEFAULT NULL,
  `AP_Date` date DEFAULT NULL,
  `Amount` decimal(15,2) DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'Pending',
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounting_payables`
--

INSERT INTO `accounting_payables` (`AP_ID`, `Supplier_ID`, `Reference_No`, `AP_Date`, `Amount`, `Status`, `Remarks`) VALUES
(1, 1, 'DR-DR 1234567', '2026-07-28', 1940000.00, 'Pending', 'Auto-generated from Goods Receipt DR-DR 1234567');

-- --------------------------------------------------------

--
-- Table structure for table `accounting_payment_vouchers`
--

CREATE TABLE `accounting_payment_vouchers` (
  `PV_ID` int(11) NOT NULL,
  `PV_No` varchar(50) DEFAULT NULL,
  `PV_Date` date DEFAULT NULL,
  `Supplier_ID` int(11) DEFAULT NULL,
  `AP_ID` int(11) DEFAULT NULL,
  `Amount` decimal(15,2) DEFAULT NULL,
  `Payment_Method` varchar(50) DEFAULT NULL,
  `Check_No` varchar(50) DEFAULT NULL,
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `Log_ID` int(11) NOT NULL,
  `Username` varchar(50) DEFAULT NULL,
  `Action` varchar(100) DEFAULT NULL,
  `Details` text DEFAULT NULL,
  `Log_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`Log_ID`, `Username`, `Action`, `Details`, `Log_Date`) VALUES
(1, 'admin', 'CREATE RECORD', 'Created Supplier: YAKULT PHILIPPINES, INC.', '2026-08-28 09:18:24');

-- --------------------------------------------------------

--
-- Table structure for table `company_profile`
--

CREATE TABLE `company_profile` (
  `Profile_ID` int(11) NOT NULL,
  `Company_Name` varchar(150) DEFAULT NULL,
  `TIN` varchar(50) DEFAULT NULL,
  `Province` varchar(100) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Barangay` varchar(100) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Contact_No` varchar(50) DEFAULT NULL,
  `Logo_Path` varchar(255) DEFAULT NULL,
  `Lock_Date` date DEFAULT NULL,
  `YL_Disc_Orig` decimal(5,3) DEFAULT 0.450,
  `YL_Disc_Light` decimal(5,3) DEFAULT 0.550,
  `YL_Trade_Orig` decimal(5,3) DEFAULT 0.500,
  `YL_Trade_Light` decimal(5,3) DEFAULT 0.700
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_profile`
--

INSERT INTO `company_profile` (`Profile_ID`, `Company_Name`, `TIN`, `Province`, `City`, `Barangay`, `Address`, `Contact_No`, `Logo_Path`, `Lock_Date`, `YL_Disc_Orig`, `YL_Disc_Light`, `YL_Trade_Orig`, `YL_Trade_Light`) VALUES
(1, 'Merchandise, Distributors, Inc.', '000-310-014-00000', 'CEBU', 'CITY OF MANDAUE', 'MANTUYONG', '2108 MF Echivarre Street', '(032) 346 8641', 'uploads/1787281130_mdi sig logo.png', NULL, 0.450, 0.550, 0.500, 0.700);

-- --------------------------------------------------------

--
-- Table structure for table `ds_collection_receipts`
--

CREATE TABLE `ds_collection_receipts` (
  `CR_ID` int(11) NOT NULL,
  `DS_Type` varchar(10) DEFAULT NULL,
  `CR_No` varchar(50) DEFAULT NULL,
  `CR_Date` date DEFAULT NULL,
  `Invoice_IDs_JSON` text DEFAULT NULL,
  `Outlet_Name` varchar(150) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Outlet_TIN` varchar(50) DEFAULT NULL,
  `Business_Style` varchar(100) DEFAULT NULL,
  `Total_Amount_Due` decimal(15,2) DEFAULT NULL,
  `Total_Amount_Words` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ds_invoices`
--

CREATE TABLE `ds_invoices` (
  `Invoice_ID` int(11) NOT NULL,
  `DS_Type` varchar(10) DEFAULT NULL,
  `Invoice_No` varchar(50) DEFAULT NULL,
  `Invoice_Date` date DEFAULT NULL,
  `SO_ID` int(11) DEFAULT NULL,
  `Net_Amount` decimal(15,2) DEFAULT NULL,
  `VAT` decimal(15,2) DEFAULT NULL,
  `Applied_EWT` tinyint(1) DEFAULT 0,
  `EWT_Amount` decimal(15,2) DEFAULT 0.00,
  `Amount_Due` decimal(15,2) DEFAULT NULL,
  `Discount_Percent` decimal(5,2) DEFAULT 0.00,
  `Discount_Amount` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ds_sales_orders`
--

CREATE TABLE `ds_sales_orders` (
  `SO_ID` int(11) NOT NULL,
  `DS_Type` varchar(10) DEFAULT NULL,
  `SO_No` varchar(50) DEFAULT NULL,
  `SO_Date` date DEFAULT NULL,
  `Outlet_ID` int(11) DEFAULT NULL,
  `Items_JSON` text DEFAULT NULL,
  `Total_Quantity` int(11) DEFAULT NULL,
  `Total_Amount` decimal(15,2) DEFAULT NULL,
  `Payment_Status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dtr`
--

CREATE TABLE `dtr` (
  `DTR_ID` int(11) NOT NULL,
  `Emp_ID` int(11) NOT NULL,
  `Cutoff_Start` date DEFAULT NULL,
  `Cutoff_End` date DEFAULT NULL,
  `Days_Worked` decimal(5,2) DEFAULT 0.00,
  `OT_Hours` decimal(5,2) DEFAULT 0.00,
  `Late_Undertime_Hours` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `Emp_ID` int(11) NOT NULL,
  `Emp_No` varchar(50) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Last_Name` varchar(50) DEFAULT NULL,
  `Position` varchar(100) DEFAULT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `Basic_Rate` decimal(15,2) DEFAULT 0.00,
  `Rate_Type` varchar(20) DEFAULT 'Daily',
  `SSS_No` varchar(50) DEFAULT NULL,
  `PhilHealth_No` varchar(50) DEFAULT NULL,
  `PagIBIG_No` varchar(50) DEFAULT NULL,
  `TIN` varchar(50) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Active',
  `Hire_Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fixed_assets`
--

CREATE TABLE `fixed_assets` (
  `Asset_ID` int(11) NOT NULL,
  `Asset_Name` varchar(255) DEFAULT NULL,
  `Purchase_Date` date DEFAULT NULL,
  `Purchase_Cost` decimal(15,2) DEFAULT NULL,
  `Useful_Life_Months` int(11) DEFAULT NULL,
  `Monthly_Depreciation` decimal(15,2) DEFAULT NULL,
  `Accumulated_Depreciation` decimal(15,2) DEFAULT 0.00,
  `Status` varchar(50) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fleet_maintenance`
--

CREATE TABLE `fleet_maintenance` (
  `Maintenance_ID` int(11) NOT NULL,
  `Vehicle_ID` int(11) DEFAULT NULL,
  `Service_Date` date DEFAULT NULL,
  `Service_Type` varchar(100) DEFAULT NULL,
  `Cost` decimal(15,2) DEFAULT NULL,
  `Remarks` text DEFAULT NULL,
  `Account_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fleet_trips`
--

CREATE TABLE `fleet_trips` (
  `Trip_ID` int(11) NOT NULL,
  `Vehicle_ID` int(11) DEFAULT NULL,
  `Trip_Date` date DEFAULT NULL,
  `Route` varchar(100) DEFAULT NULL,
  `Driver_Name` varchar(100) DEFAULT NULL,
  `Agent_Name` varchar(100) DEFAULT NULL,
  `Start_Mileage` decimal(10,2) DEFAULT NULL,
  `End_Mileage` decimal(10,2) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Dispatched'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fleet_vehicles`
--

CREATE TABLE `fleet_vehicles` (
  `Vehicle_ID` int(11) NOT NULL,
  `Plate_No` varchar(20) DEFAULT NULL,
  `Make_Model` varchar(100) DEFAULT NULL,
  `Vehicle_Type` varchar(50) DEFAULT NULL,
  `Current_Mileage` decimal(10,2) DEFAULT 0.00,
  `Status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goods_receipts`
--

CREATE TABLE `goods_receipts` (
  `Receipt_ID` int(11) NOT NULL,
  `DR_No` varchar(50) DEFAULT NULL,
  `Arrival_Date` date DEFAULT NULL,
  `PO_ID` int(11) DEFAULT NULL,
  `Warehouse_ID` int(11) DEFAULT NULL,
  `Forwarder` varchar(100) DEFAULT NULL,
  `Seal_No` varchar(50) DEFAULT NULL,
  `Items_JSON` text DEFAULT NULL,
  `Total_Received` int(11) DEFAULT NULL,
  `Total_Amount` decimal(15,2) DEFAULT 0.00,
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `goods_receipts`
--

INSERT INTO `goods_receipts` (`Receipt_ID`, `DR_No`, `Arrival_Date`, `PO_ID`, `Warehouse_ID`, `Forwarder`, `Seal_No`, `Items_JSON`, `Total_Received`, `Total_Amount`, `Remarks`) VALUES
(1, 'DR 1234567', '2026-07-28', 1, 1, 'YPI', '123123', '[{\"product_id\":\"1\",\"product_name\":\"[P-10001] YAKULT ORIGINAL (BCO)\",\"quantity\":100000,\"unit_cost\":6,\"subtotal\":600000},{\"product_id\":\"3\",\"product_name\":\"[P-10003] YAKULT ORIGINAL (CPO)\",\"quantity\":100000,\"unit_cost\":5.9,\"subtotal\":590000},{\"product_id\":\"2\",\"product_name\":\"[P-10002] YAKULT LIGHT (BCL)\",\"quantity\":50000,\"unit_cost\":7.5,\"subtotal\":375000},{\"product_id\":\"4\",\"product_name\":\"[P-10004] YAKULT LIGHT (CPL)\",\"quantity\":50000,\"unit_cost\":7.5,\"subtotal\":375000}]', 300000, 1940000.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `independent_dealers`
--

CREATE TABLE `independent_dealers` (
  `Dealer_ID` int(11) NOT NULL,
  `Dealer_No` varchar(50) DEFAULT NULL,
  `First_Name` varchar(50) DEFAULT NULL,
  `Middle_Name` varchar(50) DEFAULT NULL,
  `Last_Name` varchar(50) DEFAULT NULL,
  `Birth_Date` date DEFAULT NULL,
  `Hiring_Date` date DEFAULT NULL,
  `Center_Code` varchar(50) DEFAULT NULL,
  `Center` varchar(100) DEFAULT NULL,
  `Area` varchar(100) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `independent_dealers`
--

INSERT INTO `independent_dealers` (`Dealer_ID`, `Dealer_No`, `First_Name`, `Middle_Name`, `Last_Name`, `Birth_Date`, `Hiring_Date`, `Center_Code`, `Center`, `Area`, `Type`, `Status`, `Remarks`) VALUES
(1, '10001', 'BETTY', 'GO', 'BELMONTE', '1991-04-15', '2026-07-28', 'CB1', 'CEBU A CENTER', 'CEBU A 01', 'Yakult Lady', 'Active', ''),
(2, '10002', 'JUANA', 'DEE', 'CRUZ', '1991-07-23', '2026-07-28', 'CB2', 'CEBU B CENTER', 'CEBU B 01', 'Yakult Lady', 'Active', ''),
(3, '10003', 'TessT', 'DEE', 'GONG', '1991-01-29', '2026-07-20', 'CB2', 'CEBU B CENTER', 'CEBU B 02', 'Yakult Lady', 'Active', '');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_ledger`
--

CREATE TABLE `inventory_ledger` (
  `Ledger_ID` int(11) NOT NULL,
  `Transaction_Date` date DEFAULT NULL,
  `Warehouse_ID` int(11) DEFAULT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Transaction_Type` varchar(50) DEFAULT NULL,
  `Reference_No` varchar(100) DEFAULT NULL,
  `Qty_In` int(11) DEFAULT 0,
  `Qty_Out` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_ledger`
--

INSERT INTO `inventory_ledger` (`Ledger_ID`, `Transaction_Date`, `Warehouse_ID`, `Product_ID`, `Transaction_Type`, `Reference_No`, `Qty_In`, `Qty_Out`) VALUES
(1, '2026-07-28', 1, 1, 'Stock In', 'DR-DR 1234567', 100000, 0),
(2, '2026-07-28', 1, 3, 'Stock In', 'DR-DR 1234567', 100000, 0),
(3, '2026-07-28', 1, 2, 'Stock In', 'DR-DR 1234567', 50000, 0),
(4, '2026-07-28', 1, 4, 'Stock In', 'DR-DR 1234567', 50000, 0),
(5, '2026-08-28', 1, 3, 'Transfer Out', 'TR-TR 1234567', 0, 50000),
(6, '2026-08-28', 4, 3, 'Transfer In', 'TR-TR 1234567', 50000, 0),
(7, '2026-08-28', 1, 4, 'Transfer Out', 'TR-TR 1234567', 0, 20000),
(8, '2026-08-28', 4, 4, 'Transfer In', 'TR-TR 1234567', 20000, 0),
(9, '2026-08-28', 1, 3, 'Transfer Out', 'TR-TR 1236548', 0, 20000),
(10, '2026-08-28', 5, 3, 'Transfer In', 'TR-TR 1236548', 20000, 0),
(11, '2026-08-28', 1, 4, 'Transfer Out', 'TR-TR 1236548', 0, 10000),
(12, '2026-08-28', 5, 4, 'Transfer In', 'TR-TR 1236548', 10000, 0);

-- --------------------------------------------------------

--
-- Table structure for table `outlets`
--

CREATE TABLE `outlets` (
  `Outlet_ID` int(11) NOT NULL,
  `Outlet_No` varchar(50) DEFAULT NULL,
  `Outlet_Name` varchar(150) DEFAULT NULL,
  `Branch` varchar(100) DEFAULT NULL,
  `Outlet_TIN` varchar(50) DEFAULT NULL,
  `Province` varchar(100) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Barangay` varchar(100) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Route` varchar(100) DEFAULT NULL,
  `Contact_Person` varchar(100) DEFAULT NULL,
  `Contact_No` varchar(50) DEFAULT NULL,
  `Terms` varchar(50) DEFAULT NULL,
  `Business_Style` varchar(100) DEFAULT NULL,
  `DS_Section` varchar(50) DEFAULT NULL,
  `Category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outlets`
--

INSERT INTO `outlets` (`Outlet_ID`, `Outlet_No`, `Outlet_Name`, `Branch`, `Outlet_TIN`, `Province`, `City`, `Barangay`, `Address`, `Route`, `Contact_Person`, `Contact_No`, `Terms`, `Business_Style`, `DS_Section`, `Category`) VALUES
(1, '100001', 'ROSE PHARMACY INC.', 'SM', '111-222-333', 'Cebu', 'City of Cebu', 'Mambaling', 'COASTAL ROAD', 'ROUTE 1', 'JUAN LUNA', '09662314568', '15', 'PHARMACY', 'Route', 'DRUG STORE');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_records`
--

CREATE TABLE `payroll_records` (
  `Payroll_ID` int(11) NOT NULL,
  `Emp_ID` int(11) NOT NULL,
  `Cutoff_Start` date DEFAULT NULL,
  `Cutoff_End` date DEFAULT NULL,
  `Gross_Pay` decimal(15,2) DEFAULT 0.00,
  `SSS_Deduct` decimal(15,2) DEFAULT 0.00,
  `PHIC_Deduct` decimal(15,2) DEFAULT 0.00,
  `HDMF_Deduct` decimal(15,2) DEFAULT 0.00,
  `Tax_Deduct` decimal(15,2) DEFAULT 0.00,
  `Net_Pay` decimal(15,2) DEFAULT 0.00,
  `Date_Generated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `Product_ID` int(11) NOT NULL,
  `Product_No` varchar(50) DEFAULT NULL,
  `Product_Name` varchar(150) DEFAULT NULL,
  `Category` varchar(100) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `Barcode` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`Product_ID`, `Product_No`, `Product_Name`, `Category`, `Description`, `CreatedDate`, `Barcode`) VALUES
(1, 'P-10001', 'YAKULT ORIGINAL (BCO)', 'CULTURED MILK', '', '2026-08-27 07:30:55', NULL),
(2, 'P-10002', 'YAKULT LIGHT (BCL)', 'CULTURED MILK', '', '2026-08-27 07:31:06', NULL),
(3, 'P-10003', 'YAKULT ORIGINAL (CPO)', 'CULTURED MILK', '', '2026-08-27 07:31:18', NULL),
(4, 'P-10004', 'YAKULT LIGHT (CPL)', 'CULTURED MILK', '', '2026-08-27 07:31:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_pricing`
--

CREATE TABLE `product_pricing` (
  `Pricing_ID` int(11) NOT NULL,
  `Product_ID` int(11) DEFAULT NULL,
  `Unit_Cost` decimal(15,2) DEFAULT NULL,
  `Wholesale` decimal(15,2) DEFAULT NULL,
  `Retail` decimal(15,2) DEFAULT NULL,
  `ODL` decimal(15,2) DEFAULT NULL,
  `Effective_From` date DEFAULT NULL,
  `Effective_To` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_pricing`
--

INSERT INTO `product_pricing` (`Pricing_ID`, `Product_ID`, `Unit_Cost`, `Wholesale`, `Retail`, `ODL`, `Effective_From`, `Effective_To`) VALUES
(1, 1, 6.00, 9.00, 0.00, 0.00, '2023-03-01', NULL),
(2, 3, 5.90, 0.00, 9.55, 0.00, '2023-03-01', NULL),
(3, 2, 7.50, 10.80, 0.00, 0.00, '2023-03-01', NULL),
(4, 4, 7.50, 0.00, 11.45, 0.00, '2023-03-01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `PO_ID` int(11) NOT NULL,
  `PO_No` varchar(50) DEFAULT NULL,
  `PO_Date` date DEFAULT NULL,
  `Warehouse_ID` int(11) DEFAULT NULL,
  `Supplier_ID` int(11) DEFAULT NULL,
  `Items_JSON` text DEFAULT NULL,
  `Total_Quantity` int(11) DEFAULT NULL,
  `Total_Amount` decimal(15,2) DEFAULT 0.00,
  `Status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`PO_ID`, `PO_No`, `PO_Date`, `Warehouse_ID`, `Supplier_ID`, `Items_JSON`, `Total_Quantity`, `Total_Amount`, `Status`) VALUES
(1, 'PO 1212345', '2026-07-28', 1, 1, '[{\"product_id\":\"1\",\"product_name\":\"[P-10001] YAKULT ORIGINAL (BCO)\",\"quantity\":100000,\"unit_cost\":6,\"subtotal\":600000},{\"product_id\":\"3\",\"product_name\":\"[P-10003] YAKULT ORIGINAL (CPO)\",\"quantity\":100000,\"unit_cost\":5.9,\"subtotal\":590000},{\"product_id\":\"2\",\"product_name\":\"[P-10002] YAKULT LIGHT (BCL)\",\"quantity\":50000,\"unit_cost\":7.5,\"subtotal\":375000},{\"product_id\":\"4\",\"product_name\":\"[P-10004] YAKULT LIGHT (CPL)\",\"quantity\":50000,\"unit_cost\":7.5,\"subtotal\":375000}]', 300000, 1940000.00, 'Fully Received');

-- --------------------------------------------------------

--
-- Table structure for table `stock_returns`
--

CREATE TABLE `stock_returns` (
  `Return_ID` int(11) NOT NULL,
  `Return_No` varchar(50) DEFAULT NULL,
  `Return_Date` date DEFAULT NULL,
  `Warehouse_ID` int(11) DEFAULT NULL,
  `Return_Type` varchar(50) DEFAULT NULL,
  `Reference_No` varchar(100) DEFAULT NULL,
  `Items_JSON` text DEFAULT NULL,
  `Total_Quantity` int(11) DEFAULT NULL,
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `Transfer_ID` int(11) NOT NULL,
  `Transfer_No` varchar(50) DEFAULT NULL,
  `Transfer_Date` date DEFAULT NULL,
  `From_Warehouse_ID` int(11) DEFAULT NULL,
  `To_Warehouse_ID` int(11) DEFAULT NULL,
  `Items_JSON` text DEFAULT NULL,
  `Total_Quantity` int(11) DEFAULT NULL,
  `Remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_transfers`
--

INSERT INTO `stock_transfers` (`Transfer_ID`, `Transfer_No`, `Transfer_Date`, `From_Warehouse_ID`, `To_Warehouse_ID`, `Items_JSON`, `Total_Quantity`, `Remarks`) VALUES
(1, 'TR 1234567', '2026-08-28', 1, 4, '[{\"product_id\":\"3\",\"product_name\":\"[P-10003] YAKULT ORIGINAL (CPO)\",\"quantity\":50000},{\"product_id\":\"4\",\"product_name\":\"[P-10004] YAKULT LIGHT (CPL)\",\"quantity\":20000}]', 70000, ''),
(2, 'TR 1236548', '2026-08-28', 1, 5, '[{\"product_id\":\"3\",\"product_name\":\"[P-10003] YAKULT ORIGINAL (CPO)\",\"quantity\":20000},{\"product_id\":\"4\",\"product_name\":\"[P-10004] YAKULT LIGHT (CPL)\",\"quantity\":10000}]', 30000, '');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `Supplier_ID` int(11) NOT NULL,
  `Supplier_No` varchar(50) DEFAULT NULL,
  `Supplier_Name` varchar(150) DEFAULT NULL,
  `Province` varchar(100) DEFAULT NULL,
  `City` varchar(100) DEFAULT NULL,
  `Barangay` varchar(100) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Contact_Name` varchar(100) DEFAULT NULL,
  `Contact_No` varchar(50) DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`Supplier_ID`, `Supplier_No`, `Supplier_Name`, `Province`, `City`, `Barangay`, `Address`, `Contact_Name`, `Contact_No`, `CreatedDate`) VALUES
(1, 'S-10001', 'YAKULT PHILIPPINES, INC.', 'METRO MANILA (NCR)', 'CITY OF MANILA', 'BARANGAY 164', 'ERMITA', 'JUAN DELA CRUZ', '09221234567', '2026-08-28 09:18:24');

-- --------------------------------------------------------

--
-- Table structure for table `system_dropdowns`
--

CREATE TABLE `system_dropdowns` (
  `ID` int(11) NOT NULL,
  `Dropdown_Type` varchar(50) DEFAULT NULL,
  `Option_Value` varchar(100) DEFAULT NULL,
  `Parent_Link` varchar(100) DEFAULT NULL,
  `Route_In_Charge` varchar(150) DEFAULT NULL,
  `Center_Code` varchar(50) DEFAULT NULL,
  `Center_In_Charge` varchar(150) DEFAULT NULL,
  `Linked_Warehouse_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_dropdowns`
--

INSERT INTO `system_dropdowns` (`ID`, `Dropdown_Type`, `Option_Value`, `Parent_Link`, `Route_In_Charge`, `Center_Code`, `Center_In_Charge`, `Linked_Warehouse_ID`) VALUES
(1, 'Category', 'SARI-SARI STORE', 'MINI ROUTE', NULL, NULL, NULL, NULL),
(2, 'Category', 'MINI MART', 'ROUTE', NULL, NULL, NULL, NULL),
(3, 'Category', 'GROCERY', 'BOOKING', NULL, NULL, NULL, NULL),
(4, 'Category', 'DRUG STORE', 'ROUTE', NULL, NULL, NULL, NULL),
(5, 'Credit Terms', '30', '', NULL, NULL, NULL, NULL),
(6, 'Credit Terms', '15', '', NULL, NULL, NULL, NULL),
(7, 'Credit Terms', '7', '', NULL, NULL, NULL, NULL),
(8, 'Credit Terms', 'COD', '', NULL, NULL, NULL, NULL),
(9, 'Booking', 'BOOKING 1', '', '', NULL, NULL, 1),
(10, 'Booking', 'BOOKING 2', '', '', NULL, NULL, 1),
(11, 'Route', 'ROUTE 1', '', '', NULL, NULL, 1),
(12, 'Route', 'ROUTE 2', '', '', NULL, NULL, 1),
(13, 'Mini Route', 'MINI ROUTE 1', '', '', NULL, NULL, 1),
(14, 'Mini Route', 'MINI ROUTE 2', '', '', NULL, NULL, 1),
(15, 'Booking', 'BOOKING 6', '', '', NULL, NULL, 2),
(16, 'Route', 'ROUTE 8', '', '', NULL, NULL, 2),
(17, 'Booking', 'BOOKING 7', '', '', NULL, NULL, 3),
(18, 'Route', 'ROUTE 7', '', '', NULL, NULL, 3),
(19, 'Center', 'CEBU A CENTER', '', NULL, 'CB1', '', 4),
(20, 'Center', 'CEBU B CENTER', '', NULL, 'CB2', '', 5),
(21, 'Area', 'CEBU A 01', 'CEBU A CENTER', NULL, 'CB1 01', '', 4),
(22, 'Area', 'CEBU B 01', 'CEBU B CENTER', NULL, 'CB2 01', '', 5),
(23, 'Area', 'CEBU B 02', 'CEBU B CENTER', NULL, 'CB2 02', '', 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `User_ID` int(11) NOT NULL,
  `Username` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Role` varchar(50) DEFAULT NULL,
  `Permissions_JSON` text DEFAULT NULL,
  `Agent_Type` varchar(50) DEFAULT NULL,
  `Linked_Entity` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`User_ID`, `Username`, `Password`, `Role`, `Permissions_JSON`, `Agent_Type`, `Linked_Entity`) VALUES
(1, 'admin', '$2y$10$77uN.del70kXWPO0UK6ZJewSUQFeSJaapqZpq9PEHgMt88RSn2Zeq', 'Admin', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `Warehouse_ID` int(11) NOT NULL,
  `Warehouse_Name` varchar(100) DEFAULT NULL,
  `Location` varchar(150) DEFAULT NULL,
  `Location_Type` varchar(50) DEFAULT 'Main Warehouse',
  `Parent_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`Warehouse_ID`, `Warehouse_Name`, `Location`, `Location_Type`, `Parent_ID`) VALUES
(1, 'MANDAUE Main Warehouse', 'MANDAUE', 'Main Warehouse', NULL),
(2, 'CARCAR DEPOT', '', 'Main Warehouse', NULL),
(3, 'BOHOL DEPOT', '', 'Main Warehouse', NULL),
(4, 'CEBU A CENTER', '', 'Main Warehouse', NULL),
(5, 'CEBU B CENTER', '', 'Main Warehouse', NULL),
(6, 'TALISAY CENTER', '', 'Main Warehouse', NULL),
(7, 'MANDAUE CENTER', '', 'Main Warehouse', NULL),
(8, 'DANAO CENTER', '', 'Main Warehouse', NULL),
(9, 'LAPU LAPU CENTER', '', 'Main Warehouse', NULL),
(10, 'BOGO CENTER', '', 'Main Warehouse', NULL),
(11, 'CONSOLACION CENTER', '', 'Main Warehouse', NULL),
(12, 'MINGLANILLA CENTER', '', 'Main Warehouse', NULL),
(13, 'CARCAR CENTER', '', 'Main Warehouse', NULL),
(14, 'TOLEDO CENTER', '', 'Main Warehouse', NULL),
(15, 'MOALBOAL CENTER', '', 'Main Warehouse', NULL),
(16, 'TABILARAN CENTER', '', 'Main Warehouse', NULL),
(17, 'TUBIGON CENTER', '', 'Main Warehouse', NULL),
(18, 'UBAY CENTER', '', 'Main Warehouse', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `yl_calculated_rebates`
--

CREATE TABLE `yl_calculated_rebates` (
  `Rebate_Calc_ID` int(11) NOT NULL,
  `Center` varchar(100) DEFAULT NULL,
  `Dealer_ID` int(11) DEFAULT NULL,
  `Dealer_Name` varchar(255) DEFAULT NULL,
  `Area_No` varchar(100) DEFAULT NULL,
  `Period_Month` varchar(20) DEFAULT NULL,
  `Period_Day` int(11) DEFAULT NULL,
  `Invoice_ID` int(11) DEFAULT NULL,
  `Invoice_No` varchar(50) DEFAULT NULL,
  `Items_JSON` text DEFAULT NULL,
  `Total_Dealer_Discount` decimal(15,2) DEFAULT NULL,
  `Total_Trade_Discount` decimal(15,2) DEFAULT NULL,
  `Total_Sales_Rebate` decimal(15,2) DEFAULT NULL,
  `CreatedBy` varchar(50) DEFAULT NULL,
  `CreatedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdateBy` varchar(50) DEFAULT NULL,
  `UpdateDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Rebate_Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `yl_collection_receipts`
--

CREATE TABLE `yl_collection_receipts` (
  `CR_ID` int(11) NOT NULL,
  `CR_No` varchar(50) DEFAULT NULL,
  `CR_Date` date DEFAULT NULL,
  `Invoice_IDs_JSON` text DEFAULT NULL,
  `Dealer_Name` varchar(150) DEFAULT NULL,
  `Total_Amount_Due` decimal(15,2) DEFAULT NULL,
  `Total_Amount_Words` text DEFAULT NULL,
  `Actual_Cash_Received` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `yl_delivery_receipts`
--

CREATE TABLE `yl_delivery_receipts` (
  `DR_ID` int(11) NOT NULL,
  `DR_No` varchar(50) DEFAULT NULL,
  `DR_Date` date DEFAULT NULL,
  `SO_ID` int(11) DEFAULT NULL,
  `Items_JSON` text DEFAULT NULL,
  `Total_Quantity` int(11) DEFAULT 0,
  `Total_Amount` decimal(15,2) DEFAULT 0.00,
  `Is_Advance_Delivery` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `yl_invoices`
--

CREATE TABLE `yl_invoices` (
  `Invoice_ID` int(11) NOT NULL,
  `Invoice_No` varchar(50) DEFAULT NULL,
  `Invoice_Date` date DEFAULT NULL,
  `DR_ID` int(11) DEFAULT NULL,
  `Net_Amount` decimal(15,2) DEFAULT NULL,
  `VAT` decimal(15,2) DEFAULT NULL,
  `Amount_Due` decimal(15,2) DEFAULT NULL,
  `DR_IDs_JSON` text DEFAULT NULL,
  `DR_Nos` text DEFAULT NULL,
  `Dealer_Name` varchar(255) DEFAULT NULL,
  `Items_JSON` text DEFAULT NULL,
  `Dealer_Discount_Type` varchar(50) DEFAULT NULL,
  `Dealer_Discount_Amount` decimal(15,2) DEFAULT 0.00,
  `Discount_Orig_Amount` decimal(15,2) DEFAULT 0.00,
  `Discount_Light_Amount` decimal(15,2) DEFAULT 0.00,
  `Trade_Orig_Amount` decimal(15,2) DEFAULT 0.00,
  `Trade_Light_Amount` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `yl_rebate_matrix`
--

CREATE TABLE `yl_rebate_matrix` (
  `Rebate_ID` int(11) NOT NULL,
  `Product_Type` varchar(50) DEFAULT 'Original',
  `Min_Qty` int(11) DEFAULT NULL,
  `Max_Qty` int(11) DEFAULT NULL,
  `Rebate_Amount` decimal(10,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `yl_rebate_matrix`
--

INSERT INTO `yl_rebate_matrix` (`Rebate_ID`, `Product_Type`, `Min_Qty`, `Max_Qty`, `Rebate_Amount`) VALUES
(1, 'Original', 0, 99, 0.000),
(2, 'Original', 100, 149, 0.525),
(3, 'Original', 150, 199, 0.540),
(4, 'Original', 200, 249, 0.625),
(5, 'Original', 250, 299, 0.655),
(6, 'Original', 300, 349, 0.660),
(7, 'Original', 350, 399, 0.665),
(8, 'Original', 400, 449, 0.680),
(9, 'Original', 450, 499, 0.685),
(10, 'Original', 500, 599, 0.700),
(11, 'Original', 600, 999999, 0.710),
(12, 'Light', 0, 9, 0.000),
(13, 'Light', 10, 19, 0.660),
(14, 'Light', 20, 29, 0.680),
(15, 'Light', 30, 39, 0.690),
(16, 'Light', 40, 49, 0.700),
(17, 'Light', 50, 999999, 0.710);

-- --------------------------------------------------------

--
-- Table structure for table `yl_stock_orders`
--

CREATE TABLE `yl_stock_orders` (
  `SO_ID` int(11) NOT NULL,
  `SO_No` varchar(50) DEFAULT NULL,
  `SO_Date` date DEFAULT NULL,
  `Dealer_ID` int(11) DEFAULT NULL,
  `Items_JSON` text DEFAULT NULL,
  `Total_Quantity` int(11) DEFAULT NULL,
  `Total_Amount` decimal(15,2) DEFAULT NULL,
  `Payment_Status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounting_coa`
--
ALTER TABLE `accounting_coa`
  ADD PRIMARY KEY (`Account_ID`),
  ADD UNIQUE KEY `Account_Code` (`Account_Code`);

--
-- Indexes for table `accounting_expenses`
--
ALTER TABLE `accounting_expenses`
  ADD PRIMARY KEY (`Expense_ID`),
  ADD KEY `accounting_expenses_ibfk_1` (`Account_ID`);

--
-- Indexes for table `accounting_journal`
--
ALTER TABLE `accounting_journal`
  ADD PRIMARY KEY (`Journal_ID`);

--
-- Indexes for table `accounting_journal_lines`
--
ALTER TABLE `accounting_journal_lines`
  ADD PRIMARY KEY (`Line_ID`),
  ADD KEY `accounting_journal_lines_ibfk_1` (`Journal_ID`),
  ADD KEY `accounting_journal_lines_ibfk_2` (`Account_ID`);

--
-- Indexes for table `accounting_payables`
--
ALTER TABLE `accounting_payables`
  ADD PRIMARY KEY (`AP_ID`),
  ADD KEY `accounting_payables_ibfk_1` (`Supplier_ID`);

--
-- Indexes for table `accounting_payment_vouchers`
--
ALTER TABLE `accounting_payment_vouchers`
  ADD PRIMARY KEY (`PV_ID`),
  ADD KEY `accounting_payment_vouchers_ibfk_1` (`Supplier_ID`),
  ADD KEY `accounting_payment_vouchers_ibfk_2` (`AP_ID`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`Log_ID`);

--
-- Indexes for table `company_profile`
--
ALTER TABLE `company_profile`
  ADD PRIMARY KEY (`Profile_ID`);

--
-- Indexes for table `ds_collection_receipts`
--
ALTER TABLE `ds_collection_receipts`
  ADD PRIMARY KEY (`CR_ID`);

--
-- Indexes for table `ds_invoices`
--
ALTER TABLE `ds_invoices`
  ADD PRIMARY KEY (`Invoice_ID`),
  ADD KEY `ds_invoices_ibfk_1` (`SO_ID`);

--
-- Indexes for table `ds_sales_orders`
--
ALTER TABLE `ds_sales_orders`
  ADD PRIMARY KEY (`SO_ID`),
  ADD KEY `ds_sales_orders_ibfk_1` (`Outlet_ID`);

--
-- Indexes for table `dtr`
--
ALTER TABLE `dtr`
  ADD PRIMARY KEY (`DTR_ID`),
  ADD KEY `fk_dtr_employee` (`Emp_ID`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`Emp_ID`),
  ADD UNIQUE KEY `Emp_No` (`Emp_No`);

--
-- Indexes for table `fixed_assets`
--
ALTER TABLE `fixed_assets`
  ADD PRIMARY KEY (`Asset_ID`);

--
-- Indexes for table `fleet_maintenance`
--
ALTER TABLE `fleet_maintenance`
  ADD PRIMARY KEY (`Maintenance_ID`);

--
-- Indexes for table `fleet_trips`
--
ALTER TABLE `fleet_trips`
  ADD PRIMARY KEY (`Trip_ID`);

--
-- Indexes for table `fleet_vehicles`
--
ALTER TABLE `fleet_vehicles`
  ADD PRIMARY KEY (`Vehicle_ID`);

--
-- Indexes for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD PRIMARY KEY (`Receipt_ID`),
  ADD KEY `goods_receipts_ibfk_1` (`PO_ID`);

--
-- Indexes for table `independent_dealers`
--
ALTER TABLE `independent_dealers`
  ADD PRIMARY KEY (`Dealer_ID`);

--
-- Indexes for table `inventory_ledger`
--
ALTER TABLE `inventory_ledger`
  ADD PRIMARY KEY (`Ledger_ID`);

--
-- Indexes for table `outlets`
--
ALTER TABLE `outlets`
  ADD PRIMARY KEY (`Outlet_ID`);

--
-- Indexes for table `payroll_records`
--
ALTER TABLE `payroll_records`
  ADD PRIMARY KEY (`Payroll_ID`),
  ADD KEY `fk_payroll_employee` (`Emp_ID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`Product_ID`);

--
-- Indexes for table `product_pricing`
--
ALTER TABLE `product_pricing`
  ADD PRIMARY KEY (`Pricing_ID`),
  ADD KEY `product_pricing_ibfk_1` (`Product_ID`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`PO_ID`),
  ADD KEY `purchase_orders_ibfk_1` (`Supplier_ID`);

--
-- Indexes for table `stock_returns`
--
ALTER TABLE `stock_returns`
  ADD PRIMARY KEY (`Return_ID`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`Transfer_ID`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`Supplier_ID`);

--
-- Indexes for table `system_dropdowns`
--
ALTER TABLE `system_dropdowns`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`Warehouse_ID`);

--
-- Indexes for table `yl_calculated_rebates`
--
ALTER TABLE `yl_calculated_rebates`
  ADD PRIMARY KEY (`Rebate_Calc_ID`);

--
-- Indexes for table `yl_collection_receipts`
--
ALTER TABLE `yl_collection_receipts`
  ADD PRIMARY KEY (`CR_ID`);

--
-- Indexes for table `yl_delivery_receipts`
--
ALTER TABLE `yl_delivery_receipts`
  ADD PRIMARY KEY (`DR_ID`),
  ADD KEY `yl_delivery_receipts_ibfk_1` (`SO_ID`);

--
-- Indexes for table `yl_invoices`
--
ALTER TABLE `yl_invoices`
  ADD PRIMARY KEY (`Invoice_ID`),
  ADD KEY `yl_invoices_ibfk_1` (`DR_ID`);

--
-- Indexes for table `yl_rebate_matrix`
--
ALTER TABLE `yl_rebate_matrix`
  ADD PRIMARY KEY (`Rebate_ID`);

--
-- Indexes for table `yl_stock_orders`
--
ALTER TABLE `yl_stock_orders`
  ADD PRIMARY KEY (`SO_ID`),
  ADD KEY `yl_stock_orders_ibfk_1` (`Dealer_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounting_coa`
--
ALTER TABLE `accounting_coa`
  MODIFY `Account_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10534;

--
-- AUTO_INCREMENT for table `accounting_expenses`
--
ALTER TABLE `accounting_expenses`
  MODIFY `Expense_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `accounting_journal`
--
ALTER TABLE `accounting_journal`
  MODIFY `Journal_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `accounting_journal_lines`
--
ALTER TABLE `accounting_journal_lines`
  MODIFY `Line_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `accounting_payables`
--
ALTER TABLE `accounting_payables`
  MODIFY `AP_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `accounting_payment_vouchers`
--
ALTER TABLE `accounting_payment_vouchers`
  MODIFY `PV_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company_profile`
--
ALTER TABLE `company_profile`
  MODIFY `Profile_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ds_collection_receipts`
--
ALTER TABLE `ds_collection_receipts`
  MODIFY `CR_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ds_invoices`
--
ALTER TABLE `ds_invoices`
  MODIFY `Invoice_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ds_sales_orders`
--
ALTER TABLE `ds_sales_orders`
  MODIFY `SO_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dtr`
--
ALTER TABLE `dtr`
  MODIFY `DTR_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `Emp_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fixed_assets`
--
ALTER TABLE `fixed_assets`
  MODIFY `Asset_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fleet_maintenance`
--
ALTER TABLE `fleet_maintenance`
  MODIFY `Maintenance_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fleet_trips`
--
ALTER TABLE `fleet_trips`
  MODIFY `Trip_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fleet_vehicles`
--
ALTER TABLE `fleet_vehicles`
  MODIFY `Vehicle_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  MODIFY `Receipt_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `independent_dealers`
--
ALTER TABLE `independent_dealers`
  MODIFY `Dealer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_ledger`
--
ALTER TABLE `inventory_ledger`
  MODIFY `Ledger_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `outlets`
--
ALTER TABLE `outlets`
  MODIFY `Outlet_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payroll_records`
--
ALTER TABLE `payroll_records`
  MODIFY `Payroll_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `Product_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_pricing`
--
ALTER TABLE `product_pricing`
  MODIFY `Pricing_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `PO_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_returns`
--
ALTER TABLE `stock_returns`
  MODIFY `Return_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `Transfer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `Supplier_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_dropdowns`
--
ALTER TABLE `system_dropdowns`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `Warehouse_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `yl_calculated_rebates`
--
ALTER TABLE `yl_calculated_rebates`
  MODIFY `Rebate_Calc_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `yl_collection_receipts`
--
ALTER TABLE `yl_collection_receipts`
  MODIFY `CR_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `yl_delivery_receipts`
--
ALTER TABLE `yl_delivery_receipts`
  MODIFY `DR_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `yl_invoices`
--
ALTER TABLE `yl_invoices`
  MODIFY `Invoice_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `yl_rebate_matrix`
--
ALTER TABLE `yl_rebate_matrix`
  MODIFY `Rebate_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `yl_stock_orders`
--
ALTER TABLE `yl_stock_orders`
  MODIFY `SO_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounting_expenses`
--
ALTER TABLE `accounting_expenses`
  ADD CONSTRAINT `accounting_expenses_ibfk_1` FOREIGN KEY (`Account_ID`) REFERENCES `accounting_coa` (`Account_ID`);

--
-- Constraints for table `accounting_journal_lines`
--
ALTER TABLE `accounting_journal_lines`
  ADD CONSTRAINT `accounting_journal_lines_ibfk_1` FOREIGN KEY (`Journal_ID`) REFERENCES `accounting_journal` (`Journal_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `accounting_journal_lines_ibfk_2` FOREIGN KEY (`Account_ID`) REFERENCES `accounting_coa` (`Account_ID`);

--
-- Constraints for table `accounting_payables`
--
ALTER TABLE `accounting_payables`
  ADD CONSTRAINT `accounting_payables_ibfk_1` FOREIGN KEY (`Supplier_ID`) REFERENCES `suppliers` (`Supplier_ID`);

--
-- Constraints for table `accounting_payment_vouchers`
--
ALTER TABLE `accounting_payment_vouchers`
  ADD CONSTRAINT `accounting_payment_vouchers_ibfk_1` FOREIGN KEY (`Supplier_ID`) REFERENCES `suppliers` (`Supplier_ID`),
  ADD CONSTRAINT `accounting_payment_vouchers_ibfk_2` FOREIGN KEY (`AP_ID`) REFERENCES `accounting_payables` (`AP_ID`) ON DELETE CASCADE;

--
-- Constraints for table `ds_invoices`
--
ALTER TABLE `ds_invoices`
  ADD CONSTRAINT `ds_invoices_ibfk_1` FOREIGN KEY (`SO_ID`) REFERENCES `ds_sales_orders` (`SO_ID`) ON DELETE CASCADE;

--
-- Constraints for table `ds_sales_orders`
--
ALTER TABLE `ds_sales_orders`
  ADD CONSTRAINT `ds_sales_orders_ibfk_1` FOREIGN KEY (`Outlet_ID`) REFERENCES `outlets` (`Outlet_ID`);

--
-- Constraints for table `dtr`
--
ALTER TABLE `dtr`
  ADD CONSTRAINT `fk_dtr_employee` FOREIGN KEY (`Emp_ID`) REFERENCES `employees` (`Emp_ID`) ON DELETE CASCADE;

--
-- Constraints for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD CONSTRAINT `goods_receipts_ibfk_1` FOREIGN KEY (`PO_ID`) REFERENCES `purchase_orders` (`PO_ID`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_records`
--
ALTER TABLE `payroll_records`
  ADD CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`Emp_ID`) REFERENCES `employees` (`Emp_ID`) ON DELETE CASCADE;

--
-- Constraints for table `product_pricing`
--
ALTER TABLE `product_pricing`
  ADD CONSTRAINT `product_pricing_ibfk_1` FOREIGN KEY (`Product_ID`) REFERENCES `products` (`Product_ID`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`Supplier_ID`) REFERENCES `suppliers` (`Supplier_ID`);

--
-- Constraints for table `yl_delivery_receipts`
--
ALTER TABLE `yl_delivery_receipts`
  ADD CONSTRAINT `yl_delivery_receipts_ibfk_1` FOREIGN KEY (`SO_ID`) REFERENCES `yl_stock_orders` (`SO_ID`) ON DELETE CASCADE;

--
-- Constraints for table `yl_invoices`
--
ALTER TABLE `yl_invoices`
  ADD CONSTRAINT `yl_invoices_ibfk_1` FOREIGN KEY (`DR_ID`) REFERENCES `yl_delivery_receipts` (`DR_ID`) ON DELETE CASCADE;

--
-- Constraints for table `yl_stock_orders`
--
ALTER TABLE `yl_stock_orders`
  ADD CONSTRAINT `yl_stock_orders_ibfk_1` FOREIGN KEY (`Dealer_ID`) REFERENCES `independent_dealers` (`Dealer_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
