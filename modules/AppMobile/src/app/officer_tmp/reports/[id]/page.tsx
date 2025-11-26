export const metadata = {
  title: "Officer Report Details",
};

type PageParams = Promise<{ id: string }>;

export default async function Page({ params }: { params: PageParams }) {
  const { id } = await params;

  return (
    <main>
      <h1>Officer Report Details</h1>
      <p>Detailed view of a selected report. Currently viewing record {id}.</p>
    </main>
  );
}
