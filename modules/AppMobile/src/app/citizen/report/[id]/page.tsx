export const metadata = {
  title: "Report Details",
};

type PageParams = Promise<{ id: string }>;

export default async function Page({ params }: { params: PageParams }) {
  const { id } = await params;

  return (
    <main>
      <h1>Report Details</h1>
      <p>Detailed information for a specific citizen report. Currently viewing report {id}.</p>
    </main>
  );
}
